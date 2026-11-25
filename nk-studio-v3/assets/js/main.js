/* NK Studio — front-end behaviour (no dependencies) */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var accent = function () { return (getComputedStyle(document.documentElement).getPropertyValue('--nk-accent') || '#ff6a1a').trim(); };

  /* ---------- header: theme follows section underneath, collapses to MENU on scroll ---------- */
  var header = $('#nk-header');
  function headerState() {
    if (!header) return;
    header.classList.toggle('is-scrolled', window.scrollY > 80);
    var y = header.offsetHeight / 2, found = 'light';
    $$('[data-nk-theme]').forEach(function (el) {
      var r = el.getBoundingClientRect();
      if (r.top <= y && r.bottom >= y) found = el.getAttribute('data-nk-theme') === 'dark' ? 'dark' : 'light';
    });
    header.setAttribute('data-theme', found);
  }
  window.addEventListener('scroll', headerState, { passive: true });
  window.addEventListener('resize', headerState);
  headerState();

  /* ---------- overlay menu ---------- */
  var overlay = $('#nk-overlay'), btn = $('.nk-header__menu-btn');
  function setMenu(open) {
    if (!overlay) return;
    overlay.hidden = !open;
    document.body.classList.toggle('nk-menu-open', open);
    if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  if (btn) btn.addEventListener('click', function () { setMenu(true); });
  if (overlay) {
    $('.nk-overlay__close', overlay).addEventListener('click', function () { setMenu(false); });
    overlay.addEventListener('click', function (e) { if (e.target.closest('a')) setMenu(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMenu(false); });
  }

  /* ---------- custom cursor ---------- */
  var cur = $('.nk-cursor');
  if (cur && matchMedia('(hover:hover)').matches) {
    var x = 0, y = 0, cx = 0, cy = 0;
    document.addEventListener('mousemove', function (e) { x = e.clientX; y = e.clientY; cur.classList.add('is-on'); });
    document.addEventListener('mouseleave', function () { cur.classList.remove('is-on'); });
    document.addEventListener('mouseover', function (e) { cur.classList.toggle('is-big', !!e.target.closest('a,button,.nk-card')); });
    (function loop() { cx += (x - cx) * 0.18; cy += (y - cy) * 0.18; cur.style.transform = 'translate(' + cx + 'px,' + cy + 'px)'; requestAnimationFrame(loop); })();
  }

  /* ---------- reveal on scroll ---------- */
  var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
  }, { threshold: 0.12 }) : null;

  /* ---------- canvases: particle landscape (dark) and wire floor (light hero) ---------- */
  function startCanvas(c) {
    var kind = c.getAttribute('data-nk-canvas'), ctx = c.getContext('2d'), w, h, dpr = Math.min(window.devicePixelRatio || 1, 2);
    var mx = 0, my = 0, t = 0, running = true, col = accent();
    function size() { var r = c.parentNode.getBoundingClientRect(); w = r.width; h = r.height; c.width = w * dpr; c.height = h * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0); }
    size(); window.addEventListener('resize', size);
    window.addEventListener('mousemove', function (e) { mx = (e.clientX / innerWidth - 0.5); my = (e.clientY / innerHeight - 0.5); });
    new IntersectionObserver(function (es) { running = es[0].isIntersecting; if (running) frame(); }).observe(c);

    var COLS = kind === 'floor' ? 46 : 120, ROWS = kind === 'floor' ? 22 : 42;
    function frame() {
      if (!running || !c.isConnected) return;
      t += reduce ? 0 : 0.012;
      ctx.clearRect(0, 0, w, h);
      var horizon = kind === 'floor' ? h * 0.62 : h * 0.55, depthSpan = kind === 'floor' ? h * 0.5 : h * 0.5;
      var rgb = col;
      if (kind === 'particles') {
        for (var r = 0; r < ROWS; r++) {
          var z = r / (ROWS - 1), persp = 0.15 + z * z * 1.6, yBase = horizon + z * z * depthSpan;
          for (var i = 0; i < COLS; i++) {
            var u = i / (COLS - 1) - 0.5;
            var wave = Math.sin(u * 9 + t * 1.6 + r * 0.35) * 18 + Math.sin(u * 3.3 - t + r * 0.2) * 28 + Math.cos(r * .5 + t) * 6;
            var px = w / 2 + u * w * 1.9 * persp + mx * 60 * persp;
            var py = yBase - wave * persp + my * 20;
            var a = Math.max(0, 0.15 + z * 0.75) * (1 - Math.abs(u) * 0.9);
            ctx.globalAlpha = a; ctx.fillStyle = rgb;
            ctx.fillRect(px, py, 1.2 + z * 1.6, 1.2 + z * 1.6);
          }
        }
        // floating dust
        for (var k = 0; k < 40; k++) {
          var fx = (Math.sin(k * 12.9 + t * .2) * .5 + .5) * w, fy = (Math.cos(k * 7.7 + t * .15) * .5 + .5) * h * .6;
          ctx.globalAlpha = .5; ctx.fillStyle = rgb; ctx.fillRect(fx, fy, 1.6, 1.6);
        }
      } else {
        ctx.strokeStyle = '#fff'; ctx.lineWidth = 1;
        for (var rr = 0; rr < ROWS; rr++) {
          var zz = rr / (ROWS - 1), p = 0.15 + zz * zz * 1.6, yy = horizon + zz * zz * depthSpan;
          ctx.globalAlpha = 0.04 + zz * 0.16; ctx.beginPath();
          for (var ii = 0; ii <= COLS; ii++) {
            var uu = ii / COLS - 0.5, bow = Math.sin(uu * 5 + t) * 10 * zz + (uu * uu) * 160 * zz;
            var xx = w / 2 + uu * w * 1.8 * p + mx * 40 * p, y2 = yy - bow;
            ii ? ctx.lineTo(xx, y2) : ctx.moveTo(xx, y2);
          }
          ctx.stroke();
        }
        for (var cc = 0; cc <= COLS; cc += 2) {
          var u2 = cc / COLS - 0.5; ctx.globalAlpha = 0.08; ctx.beginPath();
          for (var r2 = 0; r2 < ROWS; r2++) {
            var z2 = r2 / (ROWS - 1), p2 = 0.15 + z2 * z2 * 1.6, bow2 = Math.sin(u2 * 5 + t) * 10 * z2 + (u2 * u2) * 160 * z2;
            var x2 = w / 2 + u2 * w * 1.8 * p2 + mx * 40 * p2, y3 = horizon + z2 * z2 * depthSpan - bow2;
            r2 ? ctx.lineTo(x2, y3) : ctx.moveTo(x2, y3);
          }
          ctx.stroke();
        }
      }
      ctx.globalAlpha = 1;
      if (!reduce) requestAnimationFrame(frame);
    }
    frame();
  }

  /* ---------- testimonials ---------- */
  function initTesti(root) {
    var logos = $$('.nk-testi__logo', root), quotes = $$('.nk-testi__quote', root), people = $$('.nk-testi__person', root), cur = 0, timer;
    function go(i) {
      cur = (i + quotes.length) % quotes.length;
      [logos, quotes, people].forEach(function (list) { list.forEach(function (el, n) { el.classList.toggle('is-active', n === cur); }); });
    }
    logos.concat(people).forEach(function (b) { b.addEventListener('click', function () { go(+b.getAttribute('data-i')); restart(); }); });
    function restart() {
      clearInterval(timer);
      var s = +root.getAttribute('data-autoplay') || 0;
      if (s > 0) timer = setInterval(function () { go(cur + 1); }, s * 1000);
    }
    restart();
  }

  /* ---------- big headline slides with scroll ---------- */
  var slides = [];
  function slideTick() {
    slides.forEach(function (el) {
      if (!el.isConnected) return;
      var inner = el.firstElementChild, r = el.getBoundingClientRect(), vh = innerHeight;
      var p = Math.min(1, Math.max(0, 1 - (r.top + r.height / 2) / vh));
      var extra = Math.max(0, inner.scrollWidth - el.clientWidth);
      inner.style.transform = 'translateX(' + (-(extra * p) - 0) + 'px)';
    });
  }
  window.addEventListener('scroll', slideTick, { passive: true });

  /* ---------- NK Social Videos: click a card, the video plays in place ---------- */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-nk-sv]');
    if (!b || b.classList.contains('is-playing')) return;
    e.preventDefault();
    var src = b.getAttribute('data-src'), kind = b.getAttribute('data-nk-sv'), el;
    if (!src) return;
    // only one video plays at a time
    $$('[data-nk-sv].is-playing').forEach(function (o) { var f = o.querySelector('.nk-sv__player'); if (f) f.remove(); o.classList.remove('is-playing'); });
    if (kind === 'file') {
      el = document.createElement('video');
      el.src = src; el.controls = true; el.autoplay = true; el.playsInline = true; el.setAttribute('playsinline', '');
    } else {
      el = document.createElement('iframe');
      el.src = src; el.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture; encrypted-media; clipboard-write');
      el.setAttribute('allowfullscreen', ''); el.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
      el.setAttribute('title', b.getAttribute('aria-label') || 'Video');
    }
    el.className = 'nk-sv__player';
    b.appendChild(el); b.classList.add('is-playing');
    if (el.play) { var p = el.play(); if (p && p.catch) p.catch(function () {}); }
  });

  document.addEventListener('keydown', function (e) {
    if ((e.key === 'Enter' || e.key === ' ') && e.target.matches && e.target.matches('[data-nk-sv]:not(.is-playing)')) { e.preventDefault(); e.target.click(); }
  });

  /* ---------- back to top ---------- */
  document.addEventListener('click', function (e) {
    var a = e.target.closest('.nk-backtop');
    if (a) { e.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
  });

  /* ---------- init (also re-runs in the Elementor editor when widgets re-render) ---------- */
  function init() {
    $$('canvas[data-nk-canvas]:not([data-nk-on])').forEach(function (c) { c.setAttribute('data-nk-on', 1); startCanvas(c); });
    $$('[data-nk-testi]:not([data-nk-on])').forEach(function (r) { r.setAttribute('data-nk-on', 1); initTesti(r); });
    $$('[data-nk-slide]:not([data-nk-on])').forEach(function (r) { r.setAttribute('data-nk-on', 1); slides.push(r); });
    $$('.nk-hero__inner > *, .nk-card, .nk-headline, .nk-testi__people').forEach(function (el) {
      if (el.hasAttribute('data-nk-r') || !io || document.body.classList.contains('elementor-editor-active')) return;
      el.setAttribute('data-nk-r', 1); el.classList.add('nk-reveal'); io.observe(el);
    });
    slideTick(); headerState();
  }
  if (document.readyState !== 'loading') init(); else document.addEventListener('DOMContentLoaded', init);
  if (document.body.classList.contains('elementor-editor-active') || window.elementorFrontend) {
    new MutationObserver(function () { init(); }).observe(document.body, { childList: true, subtree: true });
  }
})();
