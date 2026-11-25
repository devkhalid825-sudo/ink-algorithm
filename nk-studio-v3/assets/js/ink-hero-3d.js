/*!
 * INKNALGORITHM — 3D Glass Hero Scene (NK Studio theme)
 * Three.js r170 (loaded through the WordPress import map) · vanilla scroll
 * Mounts only when the "Ink Hero" Elementor widget is on the page: <div data-ink3d data-model="...glb">
 * The canvas is fixed behind the whole page; the model turns with the page scroll.
 */
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

/*
 * Boots when the page has an Ink Hero (data-ink3d) OR an "Ink" statement section.
 * In the Elementor editor widgets appear after load, so we wait for them.
 * An Ink Hero with "Show 3D glass logo" off (data-ink3d-off) disables the scene.
 */
const STOP_SELECTOR = '.nk-statement:not(.nk-statement--classic), [data-ink3d-stop]';
const DEFAULT_MODEL = new URL('../models/ink-algorithm-mark.glb', import.meta.url).href;
let booted = false;
function tryBoot() {
  if (booted || document.querySelector('.ink3d-stage')) return true;
  if (document.querySelector('[data-ink3d-off]')) return false;
  const anchor = document.querySelector('[data-ink3d]');
  if (!anchor && !document.querySelector(STOP_SELECTOR)) return false;
  booted = true;
  boot(anchor ? anchor.dataset.model : DEFAULT_MODEL);
  return true;
}
if (!tryBoot() && document.body && 'MutationObserver' in window) {
  const mo = new MutationObserver(() => { if (tryBoot()) mo.disconnect(); });
  mo.observe(document.body, { childList: true, subtree: true });
}

function boot(modelUrl) {
const CFG = {
  modelUrl: modelUrl || DEFAULT_MODEL,
  container: null,
  scrollTarget: null,
  // keep in sync with --ink-bg-* in assets/css/ink.css
  bgTop: '#ec8a4c',
  bgMid: '#d4561c',
  bgLow: '#6e2207',
  bgBottom: '#160401',
};

// v3.2: inside Elementor (editor / preview) the icon behaves EXACTLY like the live site: it fades away from the
// scroll-video section on, is not drawn behind black sections / .ink3d-hide sections, and is hidden in the mobile hero.
// EDITOR is only used to keep watching the page, because Elementor re-renders sections while you edit.
const EDITOR = document.body.classList.contains('elementor-editor-active') || /elementor-preview/.test(location.search) ||
  !!(window.elementorFrontend && window.elementorFrontend.isEditMode && window.elementorFrontend.isEditMode());
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ---------------------------------------------------------------- mount */
const host = CFG.container ? document.querySelector(CFG.container) : document.body;
const stage = document.createElement('div');
stage.className = 'ink3d-stage' + (CFG.container ? ' ink3d-stage--inline' : '');
const canvas = document.createElement('canvas');
canvas.setAttribute('aria-hidden', 'true');
stage.appendChild(canvas);
host.prepend(stage);

/* ------------------------------------------------------------- renderer */
const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: false, powerPreference: 'high-performance' });
renderer.outputColorSpace = THREE.SRGBColorSpace;
renderer.toneMapping = THREE.NeutralToneMapping;
renderer.toneMappingExposure = 1.0;
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);
camera.position.set(0, 0, 13);

/* -------------------------------------------- background (refracted by glass) */
const bgCanvas = document.createElement('canvas');
bgCanvas.width = 4; bgCanvas.height = 512;
const bgCtx = bgCanvas.getContext('2d');
const bgTex = new THREE.CanvasTexture(bgCanvas);
bgTex.colorSpace = THREE.SRGBColorSpace;
const C_TOP = new THREE.Color(CFG.bgTop), C_MID = new THREE.Color(CFG.bgMid), C_LOW = new THREE.Color(CFG.bgLow), C_BOT = new THREE.Color(CFG.bgBottom);
const C_FOOT_T = new THREE.Color('#2a0b03'), C_FOOT_B = new THREE.Color('#0b0201');
const _c = new THREE.Color();
let bgMix = -1;
function paintBg(m) {            // m: 0 = orange world, 1 = footer espresso
  if (Math.abs(m - bgMix) < 0.004) return;
  bgMix = m;
  const lin = bgCtx.createLinearGradient(0, 0, 0, bgCanvas.height);
  const stop = (o, a, b) => lin.addColorStop(o, '#' + _c.copy(a).lerp(b, m).getHexString());
  stop(0.00, C_TOP, C_FOOT_T); stop(0.38, C_MID, C_FOOT_T); stop(0.72, C_LOW, C_FOOT_B); stop(1.00, C_BOT, C_FOOT_B);
  bgCtx.fillStyle = lin; bgCtx.fillRect(0, 0, bgCanvas.width, bgCanvas.height);
  bgTex.needsUpdate = true;
}
paintBg(0);
scene.background = bgTex;

/* ---------------------------------------------------------- environment */
// Neutral studio room + a warm peach/orange softbox set, so the glass picks up
// bright white streaks and amber edges (like the reference coins).
const pmrem = new THREE.PMREMGenerator(renderer);
const envScene = new RoomEnvironment();
const addPanel = (color, intensity, w, h, pos, look) => {
  const m = new THREE.Mesh(
    new THREE.PlaneGeometry(w, h),
    new THREE.MeshBasicMaterial({ color: new THREE.Color(color).multiplyScalar(intensity), side: THREE.DoubleSide })
  );
  m.position.set(...pos); m.lookAt(...look);
  envScene.add(m);
};
addPanel('#ffd6b8', 6, 6, 2, [0, 6, 2], [0, 0, 0]);    // top strip — white/peach highlight
addPanel('#ff6a1a', 4, 3, 8, [-7, 0, 1], [0, 0, 0]);   // left — orange edge glow
addPanel('#ffb48a', 3, 3, 8, [7, 1, -2], [0, 0, 0]);   // right — warm rim
const envRT = pmrem.fromScene(envScene, 0.035);
scene.environment = envRT.texture;
scene.environmentIntensity = 1.0;

/* --------------------------------------------------------------- lights */
const key = new THREE.DirectionalLight('#fff1e6', 2.2);
key.position.set(4, 8, 6);
scene.add(key);
const rim = new THREE.DirectionalLight('#ff7a2e', 2.6);
rim.position.set(-6, -2, -4);
scene.add(rim);
scene.add(new THREE.AmbientLight('#ffd9c2', 0.35));

/* ------------------------------------------------------------ materials */
// Clear peach glass with a hint of iridescence (pink/blue sheen like the reference).
const glassBase = {
  color: '#ffffff',
  metalness: 0,
  roughness: 0.06,
  transmission: 1,
  thickness: 0.9,
  ior: 1.5,
  dispersion: 0.25,
  clearcoat: 1,
  clearcoatRoughness: 0.04,
  specularIntensity: 1,
  iridescence: 0.55,
  iridescenceIOR: 1.35,
  iridescenceThicknessRange: [120, 420],
  envMapIntensity: 1.25,
};
const glassA = new THREE.MeshPhysicalMaterial({ ...glassBase, attenuationColor: new THREE.Color('#ffc9a6'), attenuationDistance: 2.4 });
const glassB = new THREE.MeshPhysicalMaterial({ ...glassBase, attenuationColor: new THREE.Color('#ffd9cf'), attenuationDistance: 3.0 });

/* ---------------------------------------------------------------- model */
let modelSize = 4;
const pivot = new THREE.Group();     // scroll-driven transform
const floatG = new THREE.Group();    // idle float + mouse tilt
pivot.add(floatG);
scene.add(pivot);
pivot.visible = false;

new GLTFLoader().load(CFG.modelUrl, (gltf) => {
  const root = gltf.scene;
  let i = 0;
  root.traverse((o) => {
    if (!o.isMesh) return;
    // Model ships with smooth bevel normals; only compute if missing
    if (!o.geometry.attributes.normal) {
      o.geometry = o.geometry.index ? o.geometry.toNonIndexed() : o.geometry;
      o.geometry.computeVertexNormals();
    }
    o.material = (i++ % 2 === 0) ? glassA : glassB;
  });
  // center
  const box = new THREE.Box3().setFromObject(root);
  const center = box.getCenter(new THREE.Vector3());
  root.position.sub(center);
  modelSize = Math.max(box.max.x - box.min.x, box.max.y - box.min.y) || 4;
  floatG.add(root);
  pivot.visible = true;
  introStart = performance.now();
  stage.classList.add('is-ready');
}, undefined, (e) => console.error('[Ink 3D] model failed to load:', CFG.modelUrl, e));

/* ------------------------------------------------ scroll keyframes (0 → 1) */
const D = Math.PI;
// Hero icon start position (desktop). HERO_X: smaller = more to the left, larger = more to the right.
const HERO_X = 2.15, HERO_Y = 0.85, HERO_S = 1.22;   // Adjusted so icon sits higher and clears the frosted glass card cleanly
const KEYS_DESKTOP = [
  { p: 0.00, pos: [HERO_X, HERO_Y, 0.0], rot: [0, 0, 0], s: HERO_S },
  { p: 0.50, pos: [0.9, 0.20, 1.4], rot: [0.18, D * 0.95, -0.20], s: 1.28 },
  { p: 1.00, pos: [-2.6, 0.25, 0.2], rot: [-0.35, D * 2.0, 0.55], s: 1.15 },
];
const KEYS_MOBILE = [
  { p: 0.00, pos: [1.0, 1.7, -1.0], rot: [0, 0, 0], s: 1.05 },
  { p: 0.50, pos: [0.0, 0.40, 0.0], rot: [0.18, D * 0.95, -0.20], s: 0.95 },
  { p: 1.00, pos: [-0.2, -0.6, -0.6], rot: [-0.35, D * 2.0, 0.55], s: 0.74 },
];
let KEYS = KEYS_DESKTOP;

/*
 * "Stop" sections: the model glides to the centre behind them and slowly spins while
 * the section is on screen (NK Statement sections like "Empower brands." / "Align what matters.").
 * Add data-ink3d-stop to any other element to make it a stop too.
 */
function buildKeys(mobile) {
  const base = mobile ? KEYS_MOBILE : KEYS_DESKTOP;
  const stops = Array.from(document.querySelectorAll(STOP_SELECTOR));
  const vh = window.innerHeight;
  const max = Math.max(1, document.documentElement.scrollHeight - vh);
  if (!stops.length || max < vh * 0.5) return base;

  const C = mobile
    ? { stop: [0, 0.25, -2.2], s: 0.80, side: 0.9, sideS: 0.62, end: [0.2, -0.4, -0.8], endS: 0.72 }
    : { stop: [0, 0.15, -2.4], s: 1.65, side: 3.3, sideS: 1.15, end: [2.6, 0.2, -1.0], endS: 1.15 };
  const keys = [base[0]];
  const hold = Math.min(0.06, (vh * 0.35) / max);
  let lastP = 0;
  stops.forEach((el, i) => {
    const top = el.getBoundingClientRect().top + window.scrollY;
    const pc = (top + el.offsetHeight / 2 - vh / 2) / max;
    const p0 = pc - hold, p1 = pc + hold;
    if (p0 <= lastP + 0.02 || p1 >= 0.995) return;          // too close / off the end: skip
    const turn = (i + 1) * D;
    const side = (i % 2 === 0 ? -1 : 1) * C.side;
    keys.push({ p: (lastP + p0) / 2, pos: [side, -0.15, -0.6], rot: [0.25, turn - D * 0.5, -0.25 * Math.sign(side)], s: C.sideS });
    keys.push({ p: p0, pos: [...C.stop], rot: [-0.18, turn - 0.45, 0.10], s: C.s });
    keys.push({ p: p1, pos: [C.stop[0], C.stop[1] + 0.1, C.stop[2]], rot: [-0.10, turn + 0.45, -0.08], s: C.s });
    lastP = p1;
  });
  const last = keys[keys.length - 1];
  keys.push({ p: 1, pos: [...C.end], rot: [-0.3, last.rot[1] + D * 0.6, 0.4], s: C.endS });
  return keys;
}
document.documentElement.classList.add('ink3d-on');

const ease = (t) => t * t * (3 - 2 * t);
const lerp = (a, b, t) => a + (b - a) * t;
function sample(p, out) {
  let a = KEYS[0], b = KEYS[KEYS.length - 1];
  for (let k = 0; k < KEYS.length - 1; k++) {
    if (p >= KEYS[k].p && p <= KEYS[k + 1].p) { a = KEYS[k]; b = KEYS[k + 1]; break; }
  }
  const t = ease(b.p === a.p ? 0 : (p - a.p) / (b.p - a.p));
  for (let j = 0; j < 3; j++) { out.pos[j] = lerp(a.pos[j], b.pos[j], t); out.rot[j] = lerp(a.rot[j], b.rot[j], t); }
  out.s = lerp(a.s, b.s, t);
  return out;
}

function scrollProgress() {
  if (CFG.scrollTarget) {
    const el = document.querySelector(CFG.scrollTarget);
    if (el) {
      const r = el.getBoundingClientRect();
      const range = Math.max(1, r.height - window.innerHeight);
      return THREE.MathUtils.clamp(-r.top / range, 0, 1);
    }
  }
  const max = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
  return THREE.MathUtils.clamp(window.scrollY / max, 0, 1);
}

/* ----------------------------------------------------------- interaction */
let mx = 0, my = 0, tmx = 0, tmy = 0;
// mouse HOVER left / right (no click needed): the whole environment swings toward the pointer
// (camera, glass reflections, lights, vignette, grain) and settles back when the pointer leaves.
let dragT = 0, dragV = 0, dragTY = 0, dragVY = 0;
let hoverT = 0, hoverV = 0;   // 1 while the pointer is on the page: the model swells a little and follows it
const hoverOK = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
window.addEventListener('pointermove', (e) => {
  if (e.pointerType === 'touch') return;
  hoverT = 1;
  tmx = (e.clientX / window.innerWidth) * 2 - 1;
  tmy = (e.clientY / window.innerHeight) * 2 - 1;
  if (hoverOK) {
    dragT = THREE.MathUtils.clamp(tmx * 1.15, -1, 1);
    dragTY = THREE.MathUtils.clamp(tmy * 0.8, -1, 1);
  }
}, { passive: true });
const endHover = () => { dragT = 0; dragTY = 0; tmx = 0; tmy = 0; hoverT = 0; };
document.documentElement.addEventListener('mouseleave', endHover);
window.addEventListener('blur', endHover);

/* ---------------------------------------------------------------- resize */
function resize() {
  const w = CFG.container ? stage.clientWidth : window.innerWidth;
  const h = CFG.container ? stage.clientHeight : window.innerHeight;
  renderer.setSize(w, h, false);
  camera.aspect = w / h;
  const mobile = w < 900;
  KEYS = buildKeys(mobile);
  camera.fov = mobile ? 42 : 32;
  camera.updateProjectionMatrix();
}
window.addEventListener('resize', resize);
resize();
// page height changes (images, fonts, Elementor) -> re-measure the stop sections
let rk;
const rebuild = () => { clearTimeout(rk); rk = setTimeout(() => { KEYS = buildKeys(window.innerWidth < 900); if (typeof refreshEls === 'function') refreshEls(); }, 150); };
window.addEventListener('load', rebuild);
if ('ResizeObserver' in window) new ResizeObserver(rebuild).observe(document.body);

/* ------------------------------------------------------------------ loop */
let visible = true;
new IntersectionObserver(([en]) => { visible = en.isIntersecting; }).observe(stage);

let introStart = 0;
let dock = 0, hpS = 0;
const htmlEl = document.documentElement;
let heroEl = document.querySelector('.ink-hero');
// hero bob curve (scroll through hero 0 -> 1): up once, back down, then further down, then hand back to the path
const BOB = [[0, 0], [0.3, 0.22], [0.62, 0.0], [1.0, -0.12], [1.6, 0]];   // softened dip so icon clears the hero text card cleanly
function bobAt(h) {
  for (let k = 0; k < BOB.length - 1; k++) {
    const [a0, v0] = BOB[k], [a1, v1] = BOB[k + 1];
    if (h <= a1) return lerp(v0, v1, ease((h - a0) / (a1 - a0)));
  }
  return 0;
}
const DARK_SELECTOR = '.nk-theme-dark:not(.nk-statement--ink):not(.nk-footer):not(.ink-hero), [data-ink3d-hide], .ink3d-hide';
let darkEls = [];
let footerEl = null, videoEl = null, dockEl = null, offEl = null;
const findDark = () => { darkEls = Array.from(document.querySelectorAll(DARK_SELECTOR)).filter((el) => !el.closest('.nk-footer') && !(el.parentElement && el.parentElement.closest(DARK_SELECTOR))); };
// all page elements are looked up again whenever the page changes (Elementor re-renders a section while you edit,
// so element references kept from page load would point to removed nodes and the hiding would stop working)
function refreshEls() {
  heroEl = document.querySelector('.ink-hero');
  footerEl = document.querySelector('.nk-footer');
  videoEl = document.querySelector('.ink-video');   // icon is hidden from the video section onward
  dockEl = document.querySelector('[data-ink3d-dock]');
  offEl = document.querySelector('[data-ink3d-off]');   // Ink Hero > "Show 3D glass logo" switched off
  findDark();
}
refreshEls();
window.addEventListener('load', refreshEls);
if ('MutationObserver' in window) {
  let mt;
  new MutationObserver((list) => {
    // ignore our own canvas / flying video nodes
    if (list.every((m) => m.target === stage || stage.contains(m.target))) return;
    clearTimeout(mt);
    mt = setTimeout(() => { refreshEls(); KEYS = buildKeys(window.innerWidth < 900); }, EDITOR ? 120 : 400);
  }).observe(document.body, { childList: true, subtree: true });
}
let sp = scrollProgress();
const cur = { pos: [0, 0, 0], rot: [0, 0, 0], s: 1 };
const clock = new THREE.Clock();

renderer.setAnimationLoop(() => {
  if (!visible || document.hidden) return;
  const t = clock.getElapsedTime();

  // damped scroll — gives that buttery "scrub" feel
  sp += (scrollProgress() - sp) * (reduceMotion ? 1 : 0.075);
  sample(sp, cur);

  // intro: rises in & settles
  const it = introStart ? Math.min(1, (performance.now() - introStart) / 1800) : 0;
  const ie = 1 - Math.pow(1 - it, 4);

  const vhNow = window.innerHeight;

  /* --- hero: while scrolling through the hero the icon goes up once, comes down, then further down --- */
  const hh = heroEl ? Math.max(1, heroEl.offsetHeight) : vhNow;
  const hp = THREE.MathUtils.clamp(window.scrollY / hh, 0, 1.6);
  hpS += (hp - hpS) * (reduceMotion ? 1 : 0.08);
  const bob = reduceMotion ? 0 : bobAt(hpS);

  /* --- footer: environment turns espresso, logo shrinks and lands on the footer mark --- */
  let dk = 0;
  if (footerEl && dockEl && !(videoEl && videoEl.isConnected)) {   // icon ends at the video section: no footer landing
    const fr = footerEl.getBoundingClientRect();
    dk = THREE.MathUtils.clamp((vhNow - fr.top) / Math.min(vhNow * 0.9, fr.height), 0, 1);
  }
  dock += (dk - dock) * (reduceMotion ? 1 : 0.1);
  const de = ease(dock);
  paintBg(THREE.MathUtils.clamp(dock * 1.4, 0, 1));

  let px = cur.pos[0], py = cur.pos[1] - (1 - ie) * 1.2, pz = cur.pos[2];
  let rx = cur.rot[0], ry = cur.rot[1] - (1 - ie) * 0.9, rz = cur.rot[2];
  let sc = cur.s * (0.85 + 0.15 * ie);
  py += bob;

  // hover: eased follow so the environment glides toward the pointer and settles back
  // (v3.1: much stronger, so the hover is clearly visible — the model turns toward the pointer, leans, drifts and swells)
  dragV += (dragT - dragV) * 0.09;
  dragVY += (dragTY - dragVY) * 0.09;
  hoverV += (hoverT - hoverV) * 0.08;
  const free = 1 - ease(dock);
  px += dragV * 0.55 * free;
  py -= dragVY * 0.30 * free;
  ry += dragV * 0.85; rx += dragVY * 0.42; rz -= dragV * 0.14;
  sc *= 1 + 0.06 * hoverV * free;

  if (dock > 0.001 && dockEl && dockEl.isConnected) {
    const dr = dockEl.getBoundingClientRect();
    const ndc = new THREE.Vector3(((dr.left + dr.width / 2) / window.innerWidth) * 2 - 1, -((dr.top + dr.height / 2) / vhNow) * 2 + 1, 0.5);
    ndc.unproject(camera).sub(camera.position).normalize();
    const tz = 0;
    const tt = (tz - camera.position.z) / ndc.z;
    const tx = camera.position.x + ndc.x * tt, ty = camera.position.y + ndc.y * tt;
    const worldPerPx = (2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)) * (camera.position.z - tz)) / vhNow;
    const ts = (dr.width * 0.92 * worldPerPx) / modelSize;
    // final landing spin: a full turn that eases out to face front
    const spin = (1 - de) * D * 2;
    const turns = Math.round(ry / (D * 2)) * D * 2;
    px = lerp(px, tx, de); py = lerp(py, ty, de); pz = lerp(pz, tz, de);
    rx = lerp(rx, 0, de); ry = lerp(ry, turns + spin, de); rz = lerp(rz, 0, de);
    sc = lerp(sc, ts, de);
  }
  // from the video section on, the icon fades/shrinks away and never comes back (also not in the footer)
  let gone = 0;
  if (videoEl && videoEl.isConnected) {
    const vr = videoEl.getBoundingClientRect();
    gone = ease(THREE.MathUtils.clamp((vhNow * 0.95 - vr.top) / (vhNow * 0.5), 0, 1));
    sc *= (1 - gone);
    ry += gone * 0.9;
  }
  // mobile: no icon in the hero (it would sit behind the headline/buttons); it appears from "Build clarity" on
  if (window.innerWidth < 900) {
    const be = document.getElementById('build');
    const bv = be ? ease(THREE.MathUtils.clamp((vhNow * 0.55 - be.getBoundingClientRect().top) / (vhNow * 0.5), 0, 1)) : 0;
    sc *= bv;
  }
  if (gone >= 0.999) { sc = 0; }
  if (offEl && offEl.isConnected) { sc = 0; }                     // 3D logo switched off in the Ink Hero widget                      // icon is finished here: nothing more is animated or drawn
  if (dockEl) dockEl.classList.toggle('is-landed', dock > 0.97 && gone < 0.5);

  pivot.position.set(px, py, pz);
  pivot.rotation.set(rx, ry, rz);
  pivot.scale.setScalar(Math.max(sc, 0.0001));
  pivot.visible = sc > 0.002 && !!floatG.children.length;

  const calm = 1 - de;                               // idle float / mouse tilt fade out once landed
  mx += (tmx - mx) * 0.05;
  my += (tmy - my) * 0.05;
  const idle = reduceMotion ? 0 : calm;
  floatG.position.y = Math.sin(t * 0.9) * 0.14 * idle;
  floatG.rotation.x = Math.sin(t * 0.6) * 0.07 * idle + my * 0.22 * calm;
  floatG.rotation.y = Math.sin(t * 0.45) * 0.14 * idle + mx * 0.3 * calm + (dock > 0.97 && !reduceMotion ? Math.sin(t * 0.8) * 0.12 : 0);

  // whole environment swings with the pointer: camera orbits, reflections turn, light + vignette follow
  const yaw = dragV * 0.16 * calm;
  camera.position.set(Math.sin(yaw) * 13 + mx * 0.05 * calm, -my * 0.03 * calm - dragVY * 0.1 * calm, Math.cos(yaw) * 13);
  camera.lookAt(dragV * 0.1 * calm, 0, 0);
  if (scene.environmentRotation) scene.environmentRotation.y = dragV * 0.25;
  key.position.x = 4 + dragV * 1.2;
  rim.position.x = -6 + dragV * 0.8;
  htmlEl.style.setProperty('--ink-drag', dragV.toFixed(3));

  /* --- black-layer sections: the logo keeps moving behind them and is simply not drawn there;
         it shows again (from its normal path) as soon as a plain section is on screen --- */
  const bands = [];
  for (let k = 0; k < darkEls.length; k++) {
    if (!darkEls[k].isConnected) continue;
    const r = darkEls[k].getBoundingClientRect();
    const top = Math.max(0, r.top), bot = Math.min(vhNow, r.bottom);
    if (bot > top) bands.push([top, bot]);
  }
  if (!bands.length || dock > 0.5) {
    renderer.render(scene, camera);
  } else {
    bands.sort((u, v) => u[0] - v[0]);
    const vis = pivot.visible;
    pivot.visible = false;
    renderer.render(scene, camera);                 // environment only
    pivot.visible = vis;
    if (vis) {
      const W = window.innerWidth;
      let y = 0;
      renderer.setScissorTest(true);
      const draw = (y0, y1) => { if (y1 - y0 < 1) return; renderer.setScissor(0, vhNow - y1, W, y1 - y0); renderer.render(scene, camera); };
      bands.forEach(([t0, t1]) => { draw(y, t0); y = Math.max(y, t1); });
      draw(y, vhNow);
      renderer.setScissorTest(false);
    }
  }
});
} // boot
