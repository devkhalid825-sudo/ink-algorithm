/* INKNALGORITHM — premium text motion: [data-ink-chars] headlines reveal letter by letter
   (rise + unblur + 3D flip), big .nk-bigtext words too. Words never break mid-word. */
(function () {
  'use strict';
  var editor = document.body.classList.contains('elementor-editor-active') || /elementor-preview/.test(location.search);
  function split(el) {
    if (el.__inkC) return; el.__inkC = 1;
    var c = 0;
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (n) {
        if (n.nodeType === 3) {
          if (!n.textContent.length) return;
          var frag = document.createDocumentFragment();
          n.textContent.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(' ')); return; }
            var w = document.createElement('span'); w.className = 'ink-w';
            Array.from(part).forEach(function (ch) {
              var s = document.createElement('span'); s.className = 'ink-ch'; s.textContent = ch;
              s.style.setProperty('--c', c++); w.appendChild(s);
            });
            frag.appendChild(w);
          });
          n.parentNode.replaceChild(frag, n);
        } else if (n.nodeType === 1 && n.tagName !== 'BR' && n.tagName !== 'SUP') {
          walk(n);
        } else if (n.nodeType === 1 && n.tagName === 'SUP') {
          n.classList.add('ink-ch'); n.style.setProperty('--c', c++);
        }
      });
    })(el);
    el.setAttribute('aria-label', el.textContent.replace(/\s+/g, ' ').trim());
    el.classList.add('ink-chars');
  }
  function init() {
    if (editor) return;
    var els = document.querySelectorAll('[data-ink-chars], .nk-bigtext__inner');
    var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
    }, { threshold: 0.2 }) : null;
    els.forEach(function (el) {
      if (el.__inkC) return;
      split(el);
      if (el.classList.contains('ink-hero__title')) el.style.setProperty('--d', '120ms');
      if (io) io.observe(el); else el.classList.add('is-in');
    });
  }
  if (document.readyState !== 'loading') init(); else document.addEventListener('DOMContentLoaded', init);
})();
