/* NK FAQ accordion: click a question to open / close its answer (one open at a time unless "several open" is on). */
(function () {
  'use strict';
  /* entrance animation: the heading and the questions come in one after another when the section scrolls into view */
  document.documentElement.classList.add('nk-faq-js');
  function watch() {
    var boxes = [].slice.call(document.querySelectorAll('[data-nk-faq]:not([data-nk-seen])'));
    boxes.forEach(function (b) {
      b.setAttribute('data-nk-seen', '1');
      if (!('IntersectionObserver' in window)) { b.classList.add('is-in'); return; }
      var io = new IntersectionObserver(function (es) {
        es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
      }, { threshold: 0.12 });
      io.observe(b);
    });
  }
  watch();
  window.addEventListener('load', watch);
  if ('MutationObserver' in window) {
    var t;
    new MutationObserver(function () { clearTimeout(t); t = setTimeout(watch, 200); }).observe(document.body, { childList: true, subtree: true });
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.nk-faq__q');
    if (!btn) return;
    var item = btn.parentNode, wrap = item.closest('[data-nk-faq]');
    if (!wrap) return;
    var open = !item.classList.contains('is-open');
    if (open && !wrap.hasAttribute('data-multi')) {
      [].slice.call(wrap.querySelectorAll('.nk-faq__item.is-open')).forEach(function (o) {
        o.classList.remove('is-open');
        o.querySelector('.nk-faq__q').setAttribute('aria-expanded', 'false');
      });
    }
    item.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();
