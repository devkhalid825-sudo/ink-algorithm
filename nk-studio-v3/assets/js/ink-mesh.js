/*
 * NK Studio — "mesh + floor" background (v3.9.1). Original code, plain Canvas 2D, no libraries.
 *   - a slowly drifting network of fine lines (each point joined to its nearest neighbours)
 *   - a perspective grid floor that glides towards the viewer
 *   - light pointer parallax
 * ONE fixed layer, permanent (it does not fade while scrolling): drawn ABOVE the opaque 3D hero canvas and under the content,
 * so it also sits behind the blog sections, the original inner pages and /blogs/. Cards are opaque, so it never shows over them.
 * Pauses when the tab is hidden; a single still frame for prefers-reduced-motion.
 */
(function () {
	'use strict';
	var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
	var pointer = { x: 0, y: 0, tx: 0, ty: 0 };
	addEventListener('pointermove', function (e) { pointer.tx = (e.clientX / innerWidth - .5) * 2; pointer.ty = (e.clientY / innerHeight - .5) * 2; }, { passive: true });

	function rnd(seed) { var x = Math.sin(seed * 127.1) * 43758.5453; return x - Math.floor(x); }

	function Mesh(cv, host, opt) {
		var ctx = cv.getContext('2d');
		if (!ctx) { return null; }
		var W = 0, H = 0, dpr = 1, pts = [], t0 = performance.now(), live = true, onScreen = true, self = this;
		function size() {
			dpr = Math.min(window.devicePixelRatio || 1, 2);
			W = opt.fixed ? innerWidth : host.clientWidth; H = opt.fixed ? innerHeight : host.clientHeight;
			if (!W || !H) { return; }
			cv.width = Math.round(W * dpr); cv.height = Math.round(H * dpr);
			cv.style.width = W + 'px'; cv.style.height = H + 'px';
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
			var n = Math.max(14, Math.min(48, Math.round(W * H / 52000)));
			if (W < 700) { n = Math.round(n * .6); }
			pts = [];
			var cols = Math.ceil(Math.sqrt(n * W / H)), rows = Math.ceil(n / cols);
			for (var i = 0; i < n; i++) {
				var c = i % cols, r = Math.floor(i / cols);
				pts.push({
					bx: (c + .15 + rnd(i + 1) * .7) / cols * W * 1.1 - W * .05,
					by: (r + .15 + rnd(i + 91) * .7) / rows * H * .74,
					ax: 10 + rnd(i + 7) * 26, ay: 10 + rnd(i + 13) * 26,
					sp: .00012 + rnd(i + 3) * .00022, ph: rnd(i + 5) * 6.28, z: .25 + rnd(i + 17) * .75
				});
			}
		}
		function draw(now) {
			if (!W) { return; }
			var t = reduce ? 0 : now - t0, i, j, p = [];
			ctx.clearRect(0, 0, W, H);
			var mx = pointer.x, my = pointer.y, boost = opt.strength || 1;
			/* perspective floor (drawn first: its horizon fade must not touch the mesh lines) */
			var hz = H * .64 + my * 6, vx = W * .5 + mx * 40, base = H * 1.02, span = W * 2.4;
			ctx.save();
			var g = ctx.createLinearGradient(0, hz, 0, H);
			g.addColorStop(0, 'rgba(255,255,255,0)'); g.addColorStop(.35, 'rgba(255,255,255,.6)'); g.addColorStop(1, 'rgba(255,255,255,.95)');
			ctx.beginPath(); ctx.rect(0, hz, W, H - hz); ctx.clip();
			ctx.strokeStyle = 'rgba(255,255,255,' + (.14 * boost).toFixed(3) + ')'; ctx.lineWidth = 1;
			for (i = -22; i <= 22; i++) { ctx.beginPath(); ctx.moveTo(vx + i * 6, hz); ctx.lineTo(vx + (i * span) / 22 / 2.2, base); ctx.stroke(); }
			var ph = reduce ? .3 : (t * .00022) % 1;
			for (i = 0; i < 16; i++) {
				var u = (i + ph) / 16, yy = hz + (base - hz) * (u * u);
				ctx.strokeStyle = 'rgba(255,255,255,' + ((.05 + u * .14) * boost).toFixed(3) + ')';
				ctx.beginPath(); ctx.moveTo(0, yy); ctx.lineTo(W, yy); ctx.stroke();
			}
			ctx.globalCompositeOperation = 'destination-in'; ctx.fillStyle = g; ctx.fillRect(0, hz, W, H - hz);
			ctx.restore();
			for (i = 0; i < pts.length; i++) {
				var q = pts[i];
				p.push({ x: q.bx + Math.cos(t * q.sp + q.ph) * q.ax + mx * 34 * q.z, y: q.by + Math.sin(t * q.sp * 1.3 + q.ph) * q.ay + my * 22 * q.z, z: q.z });
			}
			var maxD = Math.max(190, W * .17);
			ctx.lineWidth = 1;
			for (i = 0; i < p.length; i++) {
				var near = [];
				for (j = 0; j < p.length; j++) {
					if (i === j) { continue; }
					var dx = p[i].x - p[j].x, dy = p[i].y - p[j].y, d = Math.sqrt(dx * dx + dy * dy);
					if (d < maxD * 1.6) { near.push([d, j]); }
				}
				near.sort(function (a, b) { return a[0] - b[0]; });
				for (var k = 0; k < Math.min(3, near.length); k++) {
					var a = (1 - near[k][0] / (maxD * 1.6)) * .3 * boost * (.5 + p[i].z * .5);
					if (a <= .01) { continue; }
					ctx.strokeStyle = 'rgba(255,255,255,' + a.toFixed(3) + ')';
					ctx.beginPath(); ctx.moveTo(p[i].x, p[i].y); ctx.lineTo(p[near[k][1]].x, p[near[k][1]].y); ctx.stroke();
				}
			}
			ctx.fillStyle = 'rgba(255,255,255,' + (.4 * boost).toFixed(2) + ')';
			for (i = 0; i < p.length; i++) { ctx.beginPath(); ctx.arc(p[i].x, p[i].y, 1.1 + p[i].z * 1.1, 0, 6.283); ctx.fill(); }

			ctx.globalAlpha = 1;
		}
		function loop(now) {
			if (!live || document.hidden || !onScreen) { self.running = false; return; }
			pointer.x += (pointer.tx - pointer.x) * .06; pointer.y += (pointer.ty - pointer.y) * .06;
			draw(now);
			requestAnimationFrame(loop);
		}
		function kick() { if (!self.running && live && !reduce) { self.running = true; requestAnimationFrame(loop); } }
		size(); draw(performance.now());
		if (!reduce) { kick(); }
		var rt; addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(function () { size(); draw(performance.now()); }, 150); });
		if (!opt.fixed && window.ResizeObserver) { new ResizeObserver(function () { clearTimeout(rt); rt = setTimeout(function () { size(); draw(performance.now()); }, 100); }).observe(host); }
		document.addEventListener('visibilitychange', kick);
		if (!opt.fixed && 'IntersectionObserver' in window) { new IntersectionObserver(function (en) { onScreen = en[0].isIntersecting; if (onScreen) { kick(); } }).observe(host); }
	}

	/* the ONE fixed page layer: permanent, behind all content (cards and sheets are opaque and cover it) */
	var b = document.body;
	if (!b.classList.contains('nk-mesh-off') && document.querySelector('.ink-bg')) {
		var fx = document.createElement('canvas');
		fx.className = 'ink-mesh ink-mesh--fixed'; fx.setAttribute('aria-hidden', 'true');
		b.appendChild(fx); // after the 3D stage in the DOM, same z-index: it paints above the opaque 3D canvas
		new Mesh(fx, b, { fixed: true });
	}

})();
