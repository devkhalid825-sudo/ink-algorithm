/* NK Statement > "Next section slides up over this one"
 * The statement ("Empower brands.", "Align what matters.") stays pinned (sticky) while the next
 * section (e.g. "Work — what we build", "Insights") rises over it as an inset card with rounded top corners that widens to full width.
 * Underneath, the statement shrinks a little and darkens. Live site only, not in the Elementor editor. */
(function () {
  'use strict';
  var body = document.body;
  if (body.classList.contains('elementor-editor-active') || /elementor-preview/.test(location.search)) return;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var markers = [].slice.call(document.querySelectorAll('[data-ink-pin]'));
  if (!markers.length) return;
  var clamp = function (v, a, b) { return Math.min(b, Math.max(a, v)); };

  /* Climb from the statement to the level that has a following sibling (widget, column or top-level container). */
  function pinLevel(sec) {
    var el = sec.closest('.elementor-widget') || sec;
    while (el && !el.nextElementSibling) {
      var up = el.parentElement && el.parentElement.closest('.elementor-element');
      if (!up) return null;
      el = up;
    }
    return el;
  }
  function solid(el) {
    var c = getComputedStyle(el).backgroundColor, m = c.match(/rgba?\(([^)]+)\)/);
    if (!m) return false;
    var p = m[1].split(',');
    return p.length < 4 || parseFloat(p[3]) > 0.92;
  }

  var pairs = [];
  markers.forEach(function (mk) {
    var sec = mk.closest('.nk-section');
    var pin = sec && pinLevel(sec);
    if (!pin || pin.classList.contains('ink-pin')) return;
    var first = pin.nextElementSibling;
    pin.classList.add('ink-pin');
    // a short first section (e.g. an awards strip) is joined with the sections after it until one screen is
    // covered, all inside ONE sheet (one glass blur, one rounded edge, no seams), so the pinned statement
    // never shows through a transparent section
    var bg = mk.getAttribute('data-bg') || 'glass', sum = 0, vh0 = window.innerHeight, fills = [];
    for (var c = first; c && sum < vh0; c = c.nextElementSibling) {
      if (c !== first && c.querySelector('[data-ink-pin], .nk-statement, .nk-footer')) break;   // stop before the next statement
      fills.push(c); sum += c.offsetHeight;
    }
    var cover = document.createElement('div');
    cover.className = 'ink-cover ink-cover-fill' + (bg === 'glass' ? ' ink-glass-fill' : '');
    first.parentNode.insertBefore(cover, first);
    fills.forEach(function (f) { cover.appendChild(f); });
    if (bg !== 'glass') {
      var allSolid = fills.every(function (f) { return solid(f) || solid(f.querySelector('.nk-section') || f); });
      if (!allSolid) cover.style.setProperty('--ink-cover-bg', bg);
    }
    cover.style.setProperty('--ink-cover-r', (parseFloat(mk.getAttribute('data-radius')) || 0) + 'px');
    // stacking: each later statement + its sheet get a higher layer than the previous pair,
    // so "Align what matters." pins exactly like "Empower brands." and Insights slides over it
    var lvl = pairs.length * 2;
    pin.style.zIndex = String(1 + lvl);
    for (var n = cover; n; n = n.nextElementSibling) { n.classList.add('ink-above'); n.style.zIndex = String(2 + lvl); }
    pairs.push({ pin: pin, cover: cover, last: cover, fills: [cover], amt: clamp(parseFloat(mk.getAttribute('data-amount')), 0, 1) || 0, p: -1 });
  });
  if (!pairs.length) return;
  document.documentElement.classList.add('ink-overlap-on');

  var ticking = false;
  function layout() {
    var vh = window.innerHeight;
    pairs.forEach(function (o) {
      // tall statements scroll to their bottom first, then hold
      o.pin.style.top = Math.min(0, vh - o.pin.offsetHeight) + 'px';
    });
    frame();
  }
  function frame() {
    ticking = false;
    var vh = window.innerHeight;
    pairs.forEach(function (o) {
      var r = o.cover.getBoundingClientRect(), top = r.top;
      var p = clamp((vh - top) / vh, 0, 1);
      // once covered, the pinned statement must not show through the (often transparent) sections after the cover
      var gh = o.last.getBoundingClientRect().bottom - top;   // height of the covering group
      var gone = top > 0 ? 0 : (gh >= vh ? 1 : clamp(-top / Math.max(1, gh * 0.6), 0, 1));
      if (Math.abs(p - o.p) < 0.0005 && gone === o.g) return;
      o.p = p; o.g = gone;
      o.pin.style.opacity = gone > 0 && gone < 1 ? (1 - gone).toFixed(3) : '';
      o.pin.style.visibility = gone >= 1 ? 'hidden' : '';
      var e = reduce ? 0 : p * p * (3 - 2 * p);
      o.pin.style.setProperty('--ink-pin-s', (1 - 0.1 * o.amt * e).toFixed(4));
      o.pin.style.setProperty('--ink-pin-y', (-6 * o.amt * e).toFixed(2) + 'vh');
      o.pin.style.setProperty('--ink-pin-dim', (0.75 * o.amt * e).toFixed(3));
      var k = (1 - e).toFixed(3);   // card is inset + rounded while rising, full width once it has covered
      o.fills.forEach(function (f) { f.style.setProperty('--ink-cover-k', k); });
    });
  }
  function request() { if (!ticking) { ticking = true; requestAnimationFrame(frame); } }
  window.addEventListener('scroll', request, { passive: true });
  window.addEventListener('resize', layout);
  window.addEventListener('load', layout);
  if ('ResizeObserver' in window) {
    var ro = new ResizeObserver(layout);
    pairs.forEach(function (o) { ro.observe(o.pin); });
  }
  layout();
})();
