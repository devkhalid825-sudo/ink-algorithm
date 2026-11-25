/* NK Studio — motion layer: Lenis smooth scroll, word/line reveals, fade-blur, parallax, PLAY cursor, testimonial rail */
(function () {
  'use strict';
  var root = document.documentElement;
  var editor = document.body.classList.contains('elementor-editor-active') || /elementor-preview/.test(location.search);
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  root.classList.add('nk-js');

  /* ---------- Lenis smooth scroll ---------- */
  var lenis = null;
  if (window.Lenis && !editor && !reduce) {
    lenis = new Lenis({ lerp: 0.1, smoothWheel: true });
    window.nkLenis = lenis;
    (function raf(t) { lenis.raf(t); requestAnimationFrame(raf); })(0);
    new MutationObserver(function () {
      document.body.classList.contains('nk-menu-open') ? lenis.stop() : lenis.start();
    }).observe(document.body, { attributes: true, attributeFilter: ['class'] });
    document.addEventListener('click', function (e) {
      var a = e.target.closest('.nk-backtop');
      if (a) { e.preventDefault(); lenis.scrollTo(0); }
    }, true);
  }

  /* ---------- in-view observer ---------- */
  var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
  }, { threshold: 0.15, rootMargin: '0px 0px -5% 0px' }) : null;

  /* ---------- titles: every word slides up inside a mask ---------- */
  function wrapUnit(node, words, prevMask) {
    if (prevMask) { prevMask.firstChild.appendChild(node); return; }
    var m = document.createElement('span'); m.className = 'nk-mask';
    var i = document.createElement('span'); i.className = 'nk-word';
    node.parentNode.insertBefore(m, node); i.appendChild(node); m.appendChild(i); words.push(i);
  }
  function splitWords(el) {
    var words = [];
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (n) {
        if (n.nodeType === 3) {
          if (!n.textContent.trim()) return;
          var frag = document.createDocumentFragment();
          n.textContent.split(/(\s+)/).forEach(function (p) {
            if (!p) return;
            if (/^\s+$/.test(p)) { frag.appendChild(document.createTextNode(' ')); return; }
            var m = document.createElement('span'); m.className = 'nk-mask';
            var i = document.createElement('span'); i.className = 'nk-word'; i.textContent = p;
            m.appendChild(i); frag.appendChild(m); words.push(i);
          });
          n.parentNode.replaceChild(frag, n);
        } else if (n.nodeType === 1 && !n.classList.contains('nk-mask')) {
          if (n.classList.contains('nk-dot')) {
            var prev = n.previousSibling;
            wrapUnit(n, words, prev && prev.classList && prev.classList.contains('nk-mask') ? prev : null);
          } else if (n.querySelector('img') || n.tagName === 'IMG' || n.classList.contains('nk-hero__thumbs')) {
            wrapUnit(n, words, null);
          } else { walk(n); }
        }
      });
    })(el);
    words.forEach(function (w, i) { w.style.setProperty('--i', i); });
    el.classList.add('nk-split');
  }

  /* ---------- paragraphs: line-by-line reveal (re-flows on resize) ---------- */
  function buildLines(el) {
    var ws = el.__nkT.split(/\s+/).filter(Boolean), spans = [], lines = [], top = null, cur = null;
    el.textContent = '';
    ws.forEach(function (w, n) { if (n) el.appendChild(document.createTextNode(' ')); var s = document.createElement('span'); s.style.display = 'inline-block'; s.textContent = w; el.appendChild(s); spans.push(s); });
    spans.forEach(function (s, i) {
      var t = s.offsetTop;
      if (top === null || Math.abs(t - top) > 4) { cur = []; lines.push(cur); top = t; }
      cur.push(ws[i]);
    });
    el.textContent = '';
    lines.forEach(function (l, i) {
      var m = document.createElement('span'); m.className = 'nk-mask nk-mask--block';
      var s = document.createElement('span'); s.className = 'nk-line'; s.style.setProperty('--i', i); s.textContent = l.join(' ');
      m.appendChild(s); el.appendChild(m);
    });
  }
  function splitLines(el) {
    if (el.children.length) return;
    el.__nkT = el.textContent.trim();
    if (!el.__nkT) return;
    buildLines(el); el.classList.add('nk-linesplit');
  }
  function rebuildLines() { $$('.nk-linesplit').forEach(function (el) { if (el.__nkT) buildLines(el); }); }
  var rt; window.addEventListener('resize', function () {
    clearTimeout(rt); rt = setTimeout(rebuildLines, 250);
  });
  // lines were measured before the web font loaded -> re-measure once DM Sans is ready
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { setTimeout(rebuildLines, 50); });

  /* ---------- parallax on card images ---------- */
  var plx = [];
  function plxTick() {
    var vh = innerHeight;
    plx.forEach(function (img) {
      var r = img.parentNode.getBoundingClientRect();
      if (r.bottom < -50 || r.top > vh + 50) return;
      var p = (r.top + r.height / 2) / vh - 0.5;
      img.style.translate = '0 ' + (p * -9).toFixed(2) + '%';
    });
  }
  window.addEventListener('scroll', plxTick, { passive: true });

  /* ---------- cursor: PLAY marquee over media cards ---------- */
  var cur = document.querySelector('.nk-cursor');
  if (cur && !cur.querySelector('.nk-cursor__play')) {
    var t = ''; for (var k = 0; k < 16; k++) t += '<span>PLAY</span>';
    cur.insertAdjacentHTML('beforeend', '<div class="nk-cursor__play"><div class="nk-cursor__track">' + t + '</div></div>');
  }
  document.addEventListener('mouseover', function (e) {
    if (!cur) return;
    cur.classList.toggle('is-play', !!e.target.closest('.nk-w-nk_work_grid .nk-card__media, .nk-card--loc'));
  });

  /* ---------- testimonial rail: logos sit on a circle, neighbours fade ---------- */
  var OP = [1, 0.4, 0.2];
  function layoutRail(box) {
    var logos = $$('.nk-testi__logo', box), n = logos.length;
    if (!n) return;
    var act = 0; logos.forEach(function (l, i) { if (l.classList.contains('is-active')) act = i; });
    logos.forEach(function (l, i) {
      var d = i - act; if (d > n / 2) d -= n; if (d < -n / 2) d += n;
      var ad = Math.abs(d), o = ad < OP.length ? OP[ad] : 0;
      l.style.transform = 'translateX(calc(-50% + ' + (d * 130) + 'px))';
      l.style.opacity = o; l.style.visibility = o ? 'visible' : 'hidden';
      l.style.pointerEvents = o ? 'auto' : 'none';
    });
  }
  function initRail(box) {
    layoutRail(box);
    new MutationObserver(function () { layoutRail(box); }).observe(box, { subtree: true, attributes: true, attributeFilter: ['class'] });
  }

  /* ---------- init ---------- */
  function init() {
    if (!editor) {
      $$('.nk-section .nk-headline:not(.nk-bigtext__inner), .nk-footer__headline').forEach(function (el) {
        if (el.__nkS) return; el.__nkS = 1;
        if (!el.hasAttribute('data-nk-dyn')) { splitWords(el); io && io.observe(el); if (!io) el.classList.add('is-in'); }
      });
      $$('.nk-hero__text, .nk-studio-sec__text, .nk-footer__text').forEach(function (el) {
        if (el.__nkP) return; el.__nkP = 1; splitLines(el); io && io.observe(el); if (!io) el.classList.add('is-in');
      });
      $$('.nk-strip__inner, .nk-testi__logos, .nk-testi__stage, .nk-studio-sec__foot, .nk-cards--loc').forEach(function (el) {
        if (el.__nkF) return; el.__nkF = 1; el.classList.add('nk-fb'); io && io.observe(el); if (!io) el.classList.add('is-in');
      });
    }
    // v3.7.2: pictures in the Insights / Studio cards and in video cards stay still (always fill their box)
    $$('.nk-card__media img').forEach(function (img) {
      if (img.closest('.nk-cards--tall, .nk-cards--loc, .nk-card__media--video')) { img.style.translate = ''; return; }
      if (plx.indexOf(img) < 0) plx.push(img);
    });
    $$('[data-nk-testi]').forEach(function (b) { if (!b.__nkR) { b.__nkR = 1; initRail(b); } });
    plxTick();
  }
  if (document.readyState !== 'loading') init(); else document.addEventListener('DOMContentLoaded', init);
  window.addEventListener('load', function () { setTimeout(init, 100); });
  if (editor) new MutationObserver(init).observe(document.body, { childList: true, subtree: true });
})();
