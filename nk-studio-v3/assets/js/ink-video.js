/* NK Scroll Video
 * 1) The video card scales / un-tilts in as it scrolls into view (Vimeo iframe loads only when near the screen).
 * 2) Keep scrolling: the same playing video shrinks and flies down to the small "landing slot"
 *    (NK Hero: right of "Build", "clarity." below — or NK Stats if turned on there) and keeps playing there, small.
 * The 3D logo is hidden from the video section onward by ink-hero-3d.js. */
(function () {
  'use strict';
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var editor = document.body.classList.contains('elementor-editor-active') || /elementor-preview/.test(location.search);
  var cards = [].slice.call(document.querySelectorAll('[data-ink-video]'));
  if (!cards.length) return;
  document.documentElement.classList.add('ink-has-video');   // footer: the 3D icon never lands there, so its empty spot is removed
  var clamp = function (v, a, b) { return Math.min(b, Math.max(a, v)); };
  var lerp = function (a, b, t) { return a + (b - a) * t; };
  var ease = function (t) { return t * t * (3 - 2 * t); };

  function load(el) {
    var f = el.querySelector('iframe[data-src]');
    if (f) { f.src = f.getAttribute('data-src'); f.removeAttribute('data-src'); }
    var v = el.querySelector('video');
    if (v && v.paused) { var p = v.play(); if (p && p.catch) p.catch(function () {}); }
  }

  /* ---- find a landing slot below the first animated card ---- */
  var main = cards.filter(function (c) { return c.getAttribute('data-ink-video') !== 'static'; })[0];
  var slot = null;
  if (main && !reduce && !editor) {
    [].slice.call(document.querySelectorAll('[data-ink-video-dock]')).some(function (s) {
      if (main.compareDocumentPosition(s) & Node.DOCUMENT_POSITION_FOLLOWING) { slot = s; return true; }   // first slot below the video
      return false;
    });
  }

  /* ---- lazy load ---- */
  var flyer = null;
  if (main && slot) {
    flyer = document.createElement('div');
    flyer.className = 'ink-video__card ink-video__fly';
    flyer.setAttribute('aria-hidden', 'true');
    [].slice.call(main.children).forEach(function (n) { flyer.appendChild(n); });
    document.body.appendChild(flyer);
    main.classList.add('is-ghost');
    slot.classList.add('is-on');
  }
  var slotR = slot ? (parseFloat(getComputedStyle(slot).borderTopLeftRadius) || 12) : 12;
  var watch = function (c) { return c === main && flyer ? flyer : c; };
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { load(watch(e.target)); io.unobserve(e.target); } });
    }, { rootMargin: '400px 0px' });
    cards.forEach(function (c) { io.observe(c); });
  } else { cards.forEach(function (c) { load(watch(c)); }); }

  if (reduce) return;

  /* ---- mode A: card animates in place (no slot on the page) ---- */
  var animated = cards.filter(function (c) { return c.getAttribute('data-ink-video') !== 'static' && !(flyer && c === main); });
  var st = animated.map(function () { return { p: 0 }; });
  var intro = 0;
  var ticking = false;

  function setLook(el, e, vz) {
    var s = el.style;
    s.setProperty('--ink-vs', (0.84 + 0.16 * e).toFixed(4));
    s.setProperty('--ink-vy', ((1 - e) * 40).toFixed(2) + 'px');
    s.setProperty('--ink-vx', ((1 - e) * 6).toFixed(3) + 'deg');
    s.setProperty('--ink-vo', (0.35 + 0.65 * e).toFixed(3));
    s.setProperty('--ink-vz', vz.toFixed(4));
  }

  function frame() {
    ticking = false;
    var vh = window.innerHeight, sy = window.scrollY, again = false;

    animated.forEach(function (c, i) {
      var r = c.parentNode.getBoundingClientRect();
      var target = clamp((vh - r.top) / (vh * 0.55 + 1), 0, 1);
      st[i].p += (target - st[i].p) * 0.14;
      if (Math.abs(target - st[i].p) > 0.002) again = true; else st[i].p = target;
      var e = ease(st[i].p);
      setLook(c, e, 1.12 - 0.12 * e);
      c.style.setProperty('--ink-vr', (28 - 10 * e).toFixed(2) + 'px');
    });

    /* ---- mode B: video flies from the big card to the landing slot ---- */
    if (flyer) {
      var cr = main.getBoundingClientRect(), sr = slot.getBoundingClientRect();
      var cDx = cr.left + cr.width / 2, cDy = cr.top + sy + cr.height / 2;
      var sDx = sr.left + sr.width / 2, sDy = sr.top + sy + sr.height / 2;
      var s0 = cDy - vh / 2, s1 = sDy - vh / 2;                 // card centred -> slot centred
      var t = s1 > s0 ? clamp((sy - s0) / (s1 - s0), 0, 1) : 0;
      var e2 = ease(t);

      var target2 = clamp((vh - cr.top) / (vh * 0.55 + 1), 0, 1);   // scale-in while arriving
      intro += (target2 - intro) * 0.14;
      if (Math.abs(target2 - intro) > 0.002) again = true; else intro = target2;
      var ie = ease(intro) * (1 - e2) + e2;                       // fully "in" once it starts shrinking

      var w = lerp(cr.width, sr.width, e2), h = lerp(cr.height, sr.height, e2);
      var x = lerp(cDx, sDx, e2) - w / 2, y = lerp(cDy, sDy, e2) - sy - h / 2;
      var fs = flyer.style;
      fs.left = x.toFixed(2) + 'px'; fs.top = y.toFixed(2) + 'px';
      fs.width = w.toFixed(2) + 'px'; fs.height = h.toFixed(2) + 'px';
      fs.setProperty('--ink-vr', lerp(lerp(28, 18, ease(intro)), slotR, e2).toFixed(2) + 'px');
      setLook(flyer, ie, 1.12 - 0.12 * ie);
      fs.opacity = '';
      // cover-fit the media (16:9 video inside any box)
      var m = flyer.querySelector('iframe, video');
      if (m) {
        m.style.width = Math.max(w, h * 16 / 9).toFixed(1) + 'px';
        m.style.height = Math.max(h, w * 9 / 16).toFixed(1) + 'px';
      }
      // after landing it simply follows the slot; hide it if the slot is far off-screen
      var off = y > vh + 200 || y + h < -200;
      fs.visibility = off ? 'hidden' : 'visible';
    }
    if (again) request();
  }
  function request() { if (!ticking) { ticking = true; requestAnimationFrame(frame); } }
  window.addEventListener('scroll', request, { passive: true });
  window.addEventListener('resize', request);
  window.addEventListener('load', request);
  if ('ResizeObserver' in window) new ResizeObserver(request).observe(document.body);
  request();
})();
