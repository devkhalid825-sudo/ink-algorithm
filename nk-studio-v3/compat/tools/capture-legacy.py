#!/usr/bin/env python3
"""Run ONCE on the server (read-only against the site, writes only the captured/ folder).

  python3 -I capture-legacy.py https://inknalgorithm.com/about/ /path/to/themes/neuros/css/theme.css
  python3 -I capture-legacy.py https://inknalgorithm.com/portfolio/ /path/to/theme.css --page   # one page -> captured/pages/portfolio.css

Captures from the LIVE original page (rendered by the real Neuros theme, so it is exact):
  inline.css      the <style id="neuros-theme-inline-css"> block (Customizer colours / fonts / header etc.)
  fonts-url.txt   the Google Fonts stylesheet URL (neuros-fonts-css)
  body-bridge.css the original <body> typography (font-family/size/colour/line-height...) re-applied to
                  `body.nk-legacy-page .nk-main`, because the NK theme sets its own body typography.
Nothing is printed except sizes. No credentials are involved.
"""
import html, os, re, sys, urllib.request

if len(sys.argv) not in (3, 4):
    sys.exit(__doc__)
url, theme_css = sys.argv[1], sys.argv[2]
page_mode = len(sys.argv) == 4 and sys.argv[3] == '--page'   # one file per page: captured/pages/<slug>.css
here = os.path.dirname(os.path.abspath(__file__))
if '/wp-content/themes/' in here.replace('\\', '/'):   # bundled in the theme: write to wp-content/uploads/nk-neuros-compat/captured (survives theme updates)
    out = os.path.join(here.replace('\\', '/').split('/wp-content/')[0], 'wp-content', 'uploads', 'nk-neuros-compat', 'captured')
else:
    out = os.path.join(here, '..', 'captured')
os.makedirs(out, exist_ok=True)
req = urllib.request.Request(url, headers={'User-Agent': 'nk-capture/1.0'})
page = urllib.request.urlopen(req, timeout=40).read().decode('utf-8', 'replace')

m = re.search(r"<style[^>]*id=['\"]neuros-theme-inline-css['\"][^>]*>(.*?)</style>", page, re.S)
inline = m.group(1).strip() if m else ''
f = re.search(r"<link[^>]*id=['\"]neuros-fonts-css['\"][^>]*href=['\"]([^'\"]+)['\"]", page) or \
    re.search(r"<link[^>]*href=['\"]([^'\"]*fonts\.googleapis\.com[^'\"]*)['\"]", page)
fonts = html.unescape(f.group(1)) if f else ''

KEEP = ('font-family', 'font-size', 'font-weight', 'line-height', 'color', 'letter-spacing', 'text-align', 'font-style')

def top_level_rules(css):
    """Yield (selector, declarations) for top-level (non @-rule) blocks; comments stripped, nesting respected."""
    css = re.sub(r"/\*.*?\*/", "", css, flags=re.S)
    i, n = 0, len(css)
    while i < n:
        j = css.find('{', i)
        if j < 0:
            return
        sel = css[i:j].strip()
        depth, k = 1, j + 1
        while k < n and depth:
            c = css[k]
            depth += (c == '{') - (c == '}')
            k += 1
        if sel and not sel.startswith('@') and '{' not in css[j + 1:k - 1]:
            yield re.sub(r"\s+", "", sel), css[j + 1:k - 1]
        i = k

decl = {}
for src in (open(theme_css, encoding='utf-8', errors='replace').read(), inline):
    for sel, body in top_level_rules(src):
        if sel in ('body', 'html,body'):
            for d in body.split(';'):
                if ':' in d:
                    k, v = d.split(':', 1)
                    k = k.strip().lower()
                    if k in KEEP and v.strip():
                        decl[k] = v.strip()
bridge = ('body.nk-legacy-page .nk-main{' + ';'.join('%s:%s' % kv for kv in decl.items()) + '}') if decl else ''

if page_mode:
    slug = [x for x in re.sub(r'[?#].*', '', url).rstrip('/').split('/') if x][-1]
    slug = 'home' if slug.endswith(('.com', '.org', '.net')) else re.sub(r'[^a-z0-9-]', '', slug.lower())
    os.makedirs(os.path.join(out, 'pages'), exist_ok=True)
    with open(os.path.join(out, 'pages', slug + '.css'), 'w', encoding='utf-8') as fh:
        fh.write(inline)
    print('%-22s %7d bytes %s' % ('pages/' + slug + '.css', len(inline.encode()), 'OK' if inline else 'EMPTY'))
    sys.exit(0)
for name, val in (('inline.css', inline), ('fonts-url.txt', fonts), ('body-bridge.css', bridge)):
    with open(os.path.join(out, name), 'w', encoding='utf-8') as fh:
        fh.write(val)
    print('%-16s %7d bytes %s' % (name, len(val.encode()), 'OK' if val else 'EMPTY'))
print('bridge properties:', ', '.join(sorted(decl)) or '-')
