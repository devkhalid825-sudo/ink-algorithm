/*
 * NK Studio — dark skin for original (Neuros / Elementor) pages. Original code.
 * The old pages were designed on white. Instead of re-building every page, the page is read as it renders:
 *   1. a light surface (white / light grey fill, no background image): a card/box becomes dark glass (nkd-s);
 *      a full-width section/row becomes transparent, so the NK gradient + mesh shows (nkd-c)
 *   2. text that would be dark on a dark background becomes light                                 (class nkd-t)
 *   3. dark borders on a dark background become light hairlines                                   (class nkd-b)
 * Images, videos, background images, brand-colour buttons and icons are left alone. Runs once, then watches for new content
 * (sliders, AJAX lists, Elementor editor edits). Only adds CSS classes to the displayed page; nothing is ever saved.
 */
(function () {
	'use strict';
	var b = document.body;
	if (!b.classList.contains('nk-legacy-page')) { return; }
	var root = document.querySelector('.nk-main .content-wrapper') || document.querySelector('.nk-main');
	if (!root) { b.classList.add('nkd-ready'); return; }

	var SKIP = { SCRIPT: 1, STYLE: 1, NOSCRIPT: 1, IMG: 1, VIDEO: 1, CANVAS: 1, IFRAME: 1, SOURCE: 1, PICTURE: 1, BR: 1, HR: 1, PATH: 1, SVG: 1, USE: 1, CIRCLE: 1, RECT: 1, G: 1, LINE: 1, POLYGON: 1, POLYLINE: 1 };
	function rgba(str) {
		var m = /rgba?\(([\d.]+)[ ,]+([\d.]+)[ ,]+([\d.]+)(?:[ ,/]+([\d.]+%?))?\)/.exec(str || '');
		if (!m) { return null; }
		var a = m[4] === undefined ? 1 : (/%$/.test(m[4]) ? parseFloat(m[4]) / 100 : parseFloat(m[4]));
		return { r: +m[1], g: +m[2], b: +m[3], a: a, l: (0.299 * m[1] + 0.587 * m[2] + 0.114 * m[3]) / 255 };
	}
	function hasOwnText(el) {
		for (var n = el.firstChild; n; n = n.nextSibling) { if (n.nodeType === 3 && /\S/.test(n.nodeValue)) { return true; } }
		return false;
	}
	/* luminance of the surface a node sits on; null = over an image (do not touch) */
	function surfaceLum(el) {
		for (var e = el; e && e !== document.body; e = e.parentElement) {
			var cs = getComputedStyle(e), bg = rgba(cs.backgroundColor);
			if (bg && bg.a > .55) { return bg.l; }
			if (/url\(/.test(cs.backgroundImage)) { return null; } // a picture behind: leave the text alone
		}
		return 0.08; // the NK page background is dark
	}
	/* a light surface: wide (a whole section / row) -> transparent, so the NK gradient + mesh shows; narrow (a card, a box) -> dark glass */
	function surfaces(list) {
		var rw = root.clientWidth || 1200;
		for (var i = 0; i < list.length; i++) {
			var el = list[i]; if (SKIP[el.tagName.toUpperCase()] || el.classList.contains('nkd-s') || el.classList.contains('nkd-c')) { continue; }
			var cs = getComputedStyle(el), bg = rgba(cs.backgroundColor);
			if (bg && bg.a > .55 && bg.l > .6 && !/url\(/.test(cs.backgroundImage)) {
				var tag = el.tagName.toUpperCase(), form = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || tag === 'BUTTON';
				var wide = !form && el.getBoundingClientRect().width >= rw * 0.82;
				el.classList.add(wide ? 'nkd-c' : 'nkd-s');
			}
		}
	}
	function texts(list) {
		for (var i = 0; i < list.length; i++) {
			var el = list[i], tag = el.tagName.toUpperCase();
			if (SKIP[tag] || el.classList.contains('nkd-t')) { continue; }
			var form = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || tag === 'BUTTON';
			if (!form && !hasOwnText(el)) { continue; }
			var sl = surfaceLum(el); if (sl === null || sl > .45) { continue; }
			var cs = getComputedStyle(el), c = rgba(cs.color);
			if (c && c.a > .2 && c.l < .55) { el.classList.add('nkd-t'); }
		}
	}
	function borders(list) {
		for (var i = 0; i < list.length; i++) {
			var el = list[i]; if (SKIP[el.tagName.toUpperCase()] || el.classList.contains('nkd-b')) { continue; }
			var cs = getComputedStyle(el);
			if (parseFloat(cs.borderTopWidth) < .5 && parseFloat(cs.borderBottomWidth) < .5 && parseFloat(cs.borderLeftWidth) < .5) { continue; }
			var bc = rgba(cs.borderTopColor), sl = surfaceLum(el);
			if (bc && bc.a > .2 && bc.l < .45 && sl !== null && sl < .45) { el.classList.add('nkd-b'); }
		}
	}
	function run(node) {
		var list = [node].concat([].slice.call(node.querySelectorAll('*')));
		[surfaces, texts, borders].forEach(function (fn) { list.forEach(function (el) { try { fn([el]); } catch (e) { /* skip this node */ } }); });
	}
	var started = false;
	function start() {
		if (started) { return; }
		started = true;
		try { run(root); } catch (e) { /* never block the page */ }
		b.classList.add('nkd-ready');
		window.addEventListener('load', function () { try { run(root); } catch (e) {} }); // late CSS / images: once more, idempotent
		if (!window.MutationObserver) { return; }
		var queue = [], t = 0;
		new MutationObserver(function (muts) {
			muts.forEach(function (m) { [].forEach.call(m.addedNodes, function (n) { if (n.nodeType === 1 && !SKIP[n.tagName.toUpperCase()]) { queue.push(n); } }); });
			if (queue.length && !t) { t = requestAnimationFrame(function () { t = 0; var q = queue.splice(0); q.forEach(function (n) { try { run(n); } catch (e) {} }); }); }
		}).observe(root, { childList: true, subtree: true });
	}
	if (document.readyState !== 'loading') { start(); } else { document.addEventListener('DOMContentLoaded', start); }
	setTimeout(start, 1400);
})();
