/* NK Studio v3.3 — Strategic Pillars motion (widget "NK Strategic Pillars (3 cards)")
 * - headline words rise out of a mask, glowing highlight words with an ink marker, subtitle fades up
 * - cards rise in one after another; 3D tilt + light + picture parallax follow the mouse
 * - soft orange light drifts after the mouse; cards move at different speeds while scrolling
 * - phones: swipe carousel with dots
 * In the Elementor editor everything is shown straight away (no text splitting, so inline editing keeps working).
 */
(function () {
  'use strict';
  var EDITOR = document.body.classList.contains('elementor-editor-active') || /[?&]elementor-preview=/.test(location.search);
  var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var FINE = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  var sections = [];

  function esc(s) { return s.replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

  /* headline -> masked words; the "highlight" words get the marker + gradient */
  function splitTitle(el, hl) {
    var text = el.textContent.replace(/\s+/g, ' ').trim();
    if (!text) return;
    var lower = text.toLowerCase(), h = (hl || '').replace(/\s+/g, ' ').trim(), at = h ? lower.indexOf(h.toLowerCase()) : -1;
    var parts = at > -1 ? [[text.slice(0, at), 0], [text.slice(at, at + h.length), 1], [text.slice(at + h.length), 0]] : [[text, 0]];
    var k = 0, html = '';
    parts.forEach(function (p) {
      var words = p[0].split(' '), out = [];
      words.forEach(function (w) { if (w) { out.push('<span class="nk-pw"><span style="--w:' + (k++) + '" data-t="' + esc(w) + '">' + esc(w) + '</span></span>'); } });
      if (!out.length) { if (p[0] === ' ') html += ' '; return; }
      var lead = /^\s/.test(p[0]) ? ' ' : '', trail = /\s$/.test(p[0]) ? ' ' : '';
      var inner = out.join(' ');
      html += lead + (p[1] ? '<span class="nk-pc__hl" style="--w:' + (k - out.length) + '">' + inner + '</span>' : inner) + trail;
    });
    el.setAttribute('aria-label', text);
    el.innerHTML = html;
  }

  function setupTilt(sec) {
    if (!FINE || REDUCED || sec.getAttribute('data-tilt') === '0') return;
    Array.prototype.forEach.call(sec.querySelectorAll('.nk-pc'), function (card) {
      var raf = 0, ev = null;
      function apply() {
        raf = 0; if (!ev || window.innerWidth <= 900) return;
        var r = card.getBoundingClientRect();
        var px = (ev.clientX - r.left) / r.width - 0.5, py = (ev.clientY - r.top) / r.height - 0.5;
        card.style.setProperty('--rx', (-py * 9).toFixed(2) + 'deg');
        card.style.setProperty('--ry', (px * 11).toFixed(2) + 'deg');
        card.style.setProperty('--gx', ((px + 0.5) * 100).toFixed(1) + '%');
        card.style.setProperty('--gy', ((py + 0.5) * 100).toFixed(1) + '%');
      }
      card.addEventListener('pointerenter', function () { card.classList.add('is-tilting'); });
      card.addEventListener('pointermove', function (e) { ev = e; if (!raf) raf = requestAnimationFrame(apply); });
      card.addEventListener('pointerleave', function () {
        ev = null; card.classList.remove('is-tilting');
        ['--rx', '--ry'].forEach(function (p) { card.style.removeProperty(p); });
      });
    });
  }

  function setupAura(sec) {
    var aura = sec.querySelector('.nk-pc__aura');
    if (!aura || REDUCED) return;
    var raf = 0, ev = null;
    sec.addEventListener('pointermove', function (e) {
      ev = e;
      if (!raf) raf = requestAnimationFrame(function () {
        raf = 0; var r = sec.getBoundingClientRect();
        sec.style.setProperty('--ax', (ev.clientX - r.left) + 'px');
        sec.style.setProperty('--ay', (ev.clientY - r.top) + 'px');
      });
    });
  }

  function setupDots(sec) {
    var grid = sec.querySelector('.nk-pc__grid'), dots = sec.querySelectorAll('.nk-pc__dots i');
    if (!grid || !dots.length) return;
    function mark() {
      var cards = grid.querySelectorAll('.nk-pc'), mid = grid.scrollLeft + grid.clientWidth / 2, best = 0, bd = 1e9;
      Array.prototype.forEach.call(cards, function (c, i) { var d = Math.abs(c.offsetLeft + c.offsetWidth / 2 - mid); if (d < bd) { bd = d; best = i; } });
      Array.prototype.forEach.call(dots, function (d, i) { d.classList.toggle('is-on', i === best); });
    }
    grid.addEventListener('scroll', function () { requestAnimationFrame(mark); }, { passive: true });
    mark();
  }

  function reveal(sec) {
    if (EDITOR || REDUCED || !('IntersectionObserver' in window)) { sec.classList.add('is-in', 'is-done'); return; }
    sec.classList.add('nk-pc-anim');
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        io.disconnect();
        requestAnimationFrame(function () { sec.classList.add('is-in'); });
        setTimeout(function () { sec.classList.add('is-done'); }, 2600);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    io.observe(sec);
  }

  function setupTitleHover(title) {
    if (!title || EDITOR || REDUCED) return;
    var busy = 0;
    function roll() {
      if (busy) return; busy = 1;
      title.classList.remove('is-roll'); void title.offsetWidth; title.classList.add('is-roll');
      var n = title.querySelectorAll('.nk-pw').length;
      setTimeout(function () { busy = 0; }, 700 + n * 45);
    }
    title.addEventListener('pointerenter', roll);
    title.addEventListener('click', roll);
  }

  function init(sec) {
    if (sec.__nkPc) return; sec.__nkPc = 1;
    var title = sec.querySelector('.nk-pc__title');
    if (title && !EDITOR && !REDUCED) { splitTitle(title, sec.getAttribute('data-highlight')); setupTitleHover(title); }
    reveal(sec);
    setupTilt(sec);
    setupAura(sec);
    setupDots(sec);
    sections.push(sec);
  }

  /* scroll parallax: cards drift at slightly different speeds (desktop, "stagger" on) */
  var SPEED = [-26, 34, -12], ticking = false;
  function parallax() {
    ticking = false;
    var vh = window.innerHeight, wide = window.innerWidth > 900;
    sections = sections.filter(function (s) { return s.isConnected; });
    sections.forEach(function (sec) {
      if (REDUCED || !sec.classList.contains('nk-pillars-x--stagger')) return;
      var r = sec.getBoundingClientRect();
      if (r.bottom < -200 || r.top > vh + 200) return;
      var p = Math.max(-1, Math.min(1, (vh / 2 - (r.top + r.height / 2)) / vh));
      Array.prototype.forEach.call(sec.querySelectorAll('.nk-pc'), function (c, i) {
        c.style.setProperty('--py', wide ? (p * SPEED[i % 3]).toFixed(1) + 'px' : '0px');
      });
    });
  }
  function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(parallax); } }
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);

  function scan() { Array.prototype.forEach.call(document.querySelectorAll('[data-nk-pillars]'), init); onScroll(); }
  if (document.readyState !== 'loading') scan(); else document.addEventListener('DOMContentLoaded', scan);
  if (EDITOR && 'MutationObserver' in window) {
    var t; new MutationObserver(function () { clearTimeout(t); t = setTimeout(scan, 80); }).observe(document.body, { childList: true, subtree: true });
  }
})();
