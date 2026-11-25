/* NK picture slider inside cards: arrows, dots, swipe / trackpad (native scroll-snap). */
(function () {
  'use strict';
  function idx(track) { return Math.round(track.scrollLeft / Math.max(1, track.clientWidth)); }
  function sync(box) {
    var track = box.querySelector('.nk-slider__track'), dots = box.querySelectorAll('.nk-slider__dots i'), n = idx(track);
    [].forEach.call(dots, function (d, i) { d.classList.toggle('is-on', i === n); });
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.nk-slider__btn');
    var dot = e.target.closest && e.target.closest('.nk-slider__dots i');
    var box = (btn || dot) && (btn || dot).closest('[data-nk-slider]');
    if (!box) return;
    e.preventDefault();
    var track = box.querySelector('.nk-slider__track'), n = track.children.length, cur = idx(track), to = cur;
    if (btn) to = btn.classList.contains('nk-slider__btn--next') ? (cur + 1) % n : (cur - 1 + n) % n;
    else to = [].indexOf.call(dot.parentNode.children, dot);
    track.scrollTo({ left: to * track.clientWidth, behavior: 'smooth' });
  });
  document.addEventListener('scroll', function (e) {
    var t = e.target;
    if (t && t.classList && t.classList.contains('nk-slider__track')) sync(t.parentNode);
  }, true);
})();
