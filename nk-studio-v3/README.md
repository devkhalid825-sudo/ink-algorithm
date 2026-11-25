# v3.12.0 (rev. 11)
* Original inner pages: the brown panel behind the content is gone: the NK orange gradient + mesh is the page, cards sit directly on it. Wide light sections become transparent, boxes/cards become dark glass.
* One container width (max 1440px) for every section; Elementor "stretched" sections (map, sliders) stay inside it.
* Forms (WPForms / contact): full-width glass fields, labels, accent button, select fixed. Text on the gradient gets a soft shadow for readability.
* The dark skin also runs inside the Elementor editor preview (display only; classes are never saved).

# v3.11.0 (rev. 10)
* ONE package: the Neuros compatibility layer (plugin nk-neuros-compat 1.4.0) is bundled in this theme (compat/). No separate plugin upload. If the old plugin is still active, deactivate it (admin notice).
* Captured original CSS now lives in wp-content/uploads/nk-neuros-compat/captured (survives theme updates); tools: compat/tools/capture-legacy.py.

# v3.10.0 (rev. 9)
* Original inner pages now live in the NK DARK theme: no white sheet. assets/js/nk-legacy-dark.js reads the rendered page and turns light surfaces into dark glass, dark text into light text, dark borders into hairlines (images, videos, background images and brand-colour buttons untouched; not run in the Elementor editor). Needs plugin nk-neuros-compat 1.4.0.
* Header/footer: the original theme's arrow bullets (ul > li:before) no longer leak into the NK menu.

# v3.9.1 (rev. 8)
* ONE permanent mesh (the home hero mesh, with its floor) behind every NK page and the blog sections; it no longer fades on scroll. Removed: the separate mesh + black background of the "Our Blogs" section and /blogs/.
* Blog cards redesigned (solid panel, chip, reading time, hover zoom, featured first article). Cards are opaque: the mesh stays behind them.

# v3.9.0 (rev. 7)
* Hero mesh: now drawn ABOVE the opaque 3D hero canvas (it used to disappear once the 3D scene faded in), stays fully visible through the hero.
* Home "Our Blogs" section: solid dark + the same mesh, no orange gradient.
* Original inner pages: same page frame as the home (NK gradient + mesh as the page background, home header untouched, original content on a white sheet); header gets the dark glass only once scrolled over the white sheet.
* Footer (all pages): CTA block melts into the black part, big INKN ALGORITHM wordmark shown in full.
* /blogs/ rebuilt in the NK dark design (inc/blogs.php + templates/blog-archive.php): real posts, category pills, pagination (?pg=N). The Elementor page itself is untouched and still opens in Elementor. Switch: Customize > NK General.

# v3.8.4 (rev. 6)
* New: "mesh + floor" background behind the home hero (original Canvas 2D code, no library). Switch: Customize > NK General > Hero mesh background.

# v3.8.3 (rev. 5)
* Header and footer show the TEXT brand (INKN ALGORITHM). Logo images / 3D mark only when ticked in Customize > NK General.
* Footer: Get in Touch, Our Location, Sitemap, Quick Links, socials, copyright (defaults for Ink & Algorithm; footer menus still win). Demo texts removed; newsletter box only when a subscribe URL is set.
* Home "Our Blogs": real posts only (the old demo nk_blog entries are never shown), "Show only these posts" picker, button "SEE MORE BLOGS" -> /blogs/.
* Social videos widget: the Videos list is now the first control, with an Add Item row, and the empty-state explains where to edit.
* Customizer defaults now also apply on the site (an unsaved option used to be empty).

# v3.8.2 (rev. 4)
* Original inner pages sit on the original WHITE canvas with dark text (the NK ink gradient is hidden behind them); NK header/footer unchanged.
* Works together with plugin nk-neuros-compat 1.2.0 (original page-title banner, blog cards, sidebar).

# v3.8.1 (rev. 3)
* Legacy-page detection no longer skips Elementor pages that contain a template reference.
* Rollback target on production is the theme `neuros` (verified: template = stylesheet = neuros). `neuros-custom` is not the live theme.
* Requires plugin `nk-neuros-compat` 1.1.0+ for the original widgets (helpers, original theme JS/CSS on original pages).

# NK Studio V3 — 3.8.0 (production-content safe)

* Nothing in the theme creates, rewrites or deletes pages, posts, menus or Elementor data automatically
  (no `after_switch_theme` import, no `admin_init` Home upgrades, no menu sync, no demo seeding).
* Original inner pages (Elementor content without NK widgets) keep their layout under the NK header/footer
  (`assets/css/legacy.css`, body class `nk-legacy-page`). Use the companion plugin **NK Neuros Compatibility**
  for the original Neuros widgets.
* The blog is the normal `post` type; the demo `nk_blog` type is inert (no `/blogs/` archive).
* Appearance > NK Studio Setup: status + explicit tools. Rebuild/demo tools need `define( 'NK_ENABLE_DEMO_TOOLS', true );`,
  package export/import needs `define( 'NK_ENABLE_PACKAGE_TOOLS', true );` in wp-config.php.
* Full notes: NK-STUDIO-V3.8.0-HANDOVER.md (delivered with the zip files).

# NK Studio theme — v3.7.5 (INKNALGORITHM)

## v3.7.5
- **Dedicated Blogs section in Dashboard**: Added a separate "Blogs" menu in the WordPress admin console (`nk_blog`).
  - Add, edit, delete, and manage multiple blogs directly from wp-admin console.
  - Dedicated fields: Blog title, description / story text, category / badge, cover image, and **Multiple Images Gallery**.
  - Multiple images uploaded via the WordPress Media Library automatically render as an interactive swipe slider with arrows (`‹ ›`) and dots on the "Our Blogs" card (`nk_studio` widget).
  - Single images render as clean static cards; videos are also supported.
  - Full export / import compatibility in "Move to another site" (`inc/migrate.php`).
  - Automatic migration seeds sample blogs from existing demo posts if empty.

## v3.7.4
- **Reviews** (NK Testimonials): new default layout "Cards" — small label, big heading "What our clients say." and a row of
  cards (photo, name, role, quote), black section. Elementor: Content > Layout (Cards / old Logo tabs), label, heading,
  cards per row, card colour. Reviews from Dashboard > Reviews still work (role + company are shown under the name).
- **Scroll video** (NK Scroll Video): Content > "Video from" = My own video (upload / Media Library) or Link
  (Vimeo / YouTube / .mp4). Cover picture, corner radius and video shape in Style.
- **Footer**: the top block (headline, Get Started, newsletter) keeps the page gradient; from the line below it the
  footer is solid black with white text (brand, link columns, bottom bar, giant wordmark).
- **"Align what matters" replaced** by a detailed section "How We Work in Any Industry" (new widget **NK How We Work
  (detail)**: label, heading, paragraphs, button, 4 icon points — all editable). Existing Home is converted once
  (backup saved: Appearance > NK Studio Setup > Restore previous Home).
- **Insights / Social Videos**: new switch "Black gradient background (no orange)" (Insights: on by default,
  Social Videos: off by default). Turn it off to get the orange page background back.


## v3.7.3
- **Reviews ("Voices") section is now solid black** (white text). Still fully editable in Elementor:
  NK Testimonials > Style > Appearance (theme / background colour / text colour / padding).
- **Stats ("3M+ / 240+ Awards") line fixed**: the orange glow of the Strategic Pillars section used to be cut off
  at the section's top edge, which drew a faint horizontal line right under the stats. The glow is now wrapped in a
  layer that fades out softly at the top and bottom edge. Nothing else changed (content stays in the database).

## v3.7.0 — one-zip migration
- Test site: NK Studio Setup > "Create zip (theme + my content)" > Download.
- Live site: upload that theme zip, Activate (or Replace) -> the bundled design (content/design.zip) is imported
  automatically once per package; uploading it back on the site that made it does nothing.

## v3.6.0 — Move to another site
- Appearance > NK Studio Setup > "Move to another site": Create package (all Elementor pages + templates + Kit
  colours/fonts + Customizer + menus + Work / Reviews / Social Videos + every picture & video) on the source site,
  Import package (file or link) on the target site. URLs are rewritten to the new domain, pages matched by slug,
  a snapshot is saved before importing. Blog posts optional (off by default).
- Activating the theme on a site that already has a homepage / posts page keeps them.

## v3.5.0 — videos in the Insights cards
- NK Insights: cards come from Blog posts / Dashboard > Social Videos / cards typed in Elementor.
- Blog posts and Work projects get a "Card video" box (YouTube / Shorts / Vimeo / Instagram / TikTok / Facebook link
  or MP4 upload). Videos play inside the card on click, or silently on a loop ("Videos on the cards").

## v3.4.0 — safe updates
- Content lives in the database, the theme zip is only code/design. Update = Appearance > Themes > Upload >
  "Replace current with uploaded" (same folder `nk-studio-v3`).
- "Re-import demo pages" never overwrites a page with content any more (it only creates missing/empty pages).
- Customizer settings are carried over automatically if the theme ever lands in a different folder (+ warning).
- Automatic snapshot of all Elementor pages/templates + Customizer on every theme version upload (last 5),
  Download backup / Restore from file — Appearance > NK Studio Setup.

## v3.3.0
- **Strategic Pillars — premium redesign** (widget "NK Strategic Pillars (3 cards)"): eyebrow (01 — 03 / label),
  big editorial headline with words rising from a mask + ink-marker highlight words (Content > "Glowing words"),
  subtitle on the right, tall glass cards with 3D tilt, light + picture parallax following the mouse, numbers,
  arrow, hover line, staggered entrance, middle card lower + scroll parallax, swipe carousel with dots on phones.
  Motion switches in Content > Motion. Files: assets/css/nk-pillars.css, assets/js/nk-pillars.js.

## v3.2.0
- **Elementor = live site**: the 3D icon is now hidden in the editor exactly like on the live site (fades away from the
  scroll-video section, not drawn behind black / `ink3d-hide` sections, hidden in the mobile hero). The Awards / Work
  sections stay solid black in the editor too. The icon script re-reads the page whenever Elementor re-renders a section.
- **Strategic Pillars (3 cards)** — widget renamed "NK Strategic Pillars (3 cards)": click a card title / text in the
  preview and type (inline editing), instant preview, columns / gap / picture shape / radius / typography controls.
  Built-in card pictures are re-pointed to this theme's files (no broken images after a folder/domain change).
- **Dashboard content**:
  - **Work** (projects): + External link, image column, order column. Shown by NK Work Grid (source "Dashboard > Work").
  - **Reviews** (new menu): name, review text, photo, company, logo, stars. NK Testimonials "Automatic" uses them as
    soon as one is published (otherwise the cards typed in Elementor).
  - **Social Videos** (new menu): YouTube / Shorts, Instagram Reels, TikTok, Facebook, Vimeo link or uploaded MP4,
    featured image = cover. New widget **NK Social Videos**; a "Social videos" section is added to Home once, after
    Testimonials (backup saved). It stays hidden on the live site until the first video is added.
- Cache busting: CSS/JS versions = theme version + file time (old cached files can no longer be served).

## v3.1.2
- Elementor editor: the 3D icon is never hidden (no black-section clipping, no fade at the video section, no mobile hiding); the black sections are see-through only inside the editor.

## v3.1.1
- Header pill + hero card glass made lighter and more see-through (no solid fill; the background blends through).

## v3.1.0
- **Glass fill**: the header menu pill and the hero description card are now real glass (light gradient fill, blur, bright rim).
- **Three feature cards** (Web & E-Commerce …): the card box around them is gone — just the picture, the text underneath
  (an uploaded image is output without any wrapper div).
- **Awards strip + "Work. what we build."** are solid black (no brown gradient).
- **Hero 3D icon hover**: now clearly reacts to the mouse — turns toward the pointer, leans, drifts, swells slightly and floats
  more. Tune in assets/js/ink-hero-3d.js ("hover" block: the 0.85 / 0.42 / 0.55 numbers).
- Everything is in the last block of assets/css/ink.css ("v3.1").

## v3.0.2
- "Empower brands." / "Align what matters.": no more edges at the sides. The sliding section is full width
  from the start and the pinned statement no longer shrinks (it still dims as it is covered).

## v3.0.1
- Empty grey box next to "Build" removed: the video landing spot is now invisible, only the video shows there.

## v3.0.0
- **Elementor new format**: Home opens in Elementor as Flexbox Containers (no more Section > Column).
  One container per section, named in the Navigator: Hero (3D), Client logos, Scroll video, Build clarity, Impact stats,
  Empower brands, Awards, Work, Align what matters, Insights, Testimonials, Studio. Each has a CSS class
  `nk-block--{key}` and an anchor id (e.g. `/#work`).
- On update (once): Elementor's Flexbox Container + Nested Elements are switched on; an old-format Home is rebuilt in the
  new format (texts/settings of NK sections are kept, a backup is saved); an already-new Home only gets the labels.
  Appearance > NK Studio Setup: "Rebuild Home in the new format" / "Restore previous Home".
- **Home components on inner pages**: every Home section is saved as an Elementor template "NK Home · …"
  (category NK Home Sections). New widget **NK Saved Section (Home components)** shows one on any page, linked —
  edit the template once and every page updates (Elementor free). Or insert a separate copy from the library
  (folder icon > My Templates). 3D scene / scripts load automatically when a saved section needs them.
- Elementor Pro Theme Builder header/footer supported (theme header/footer stay as fallback).

## v2.9.4
- Sliding section over "Empower brands." / "Align what matters." now has a **deep brown -> near-black gradient**
  (replaces the light glass) and **sharp corners** (corner radius default 0; still adjustable in NK Statement).

## v2.9.3
- Sliding section over "Empower brands." / "Align what matters." is now **light premium glass**: soft peach → apricot
  gradient with a bright glass rim on top (no more dark orange). The statement underneath fades to a warm light haze
  instead of darkening. Tweak in assets/css/ink.css (v2.9.3 block).

## v2.9.2
- Sliding section over the statements is now **orange frosted glass** (no black): one seamless glass sheet with a light top
  rim; the statement shows softly blurred through it. NK Statement > "Solid colour for the sliding section": leave empty
  for glass, or pick a colour for a solid sheet.
- "Shaped by people." ghost/stacked letters fixed (plain white text, soft edge fade kept).
- Hero 3D icon smaller and higher (and dips less on scroll) so it no longer runs into the glass text card.
  Fine-tune in assets/js/ink-hero-3d.js: HERO_X / HERO_Y / HERO_S.

## v2.9.1
- Fix: "Align what matters." now pins and Insights slides over it exactly like "Empower brands." (the second statement lost its sticky position).

## v2.9
- **Hero**: description + buttons sit on a frosted glass card, so the text stays crisp when the 3D icon passes behind.
  Ink Hero > Style > "Glass card behind description + buttons" (on/off + tint). Icon starts a bit higher and dips less.
- **Sliding section corners**: the section that rises over "Empower brands." / "Align what matters." is now an inset card
  (gap at the sides, smooth rounded top corners) that widens to full width as it covers. A short section (awards strip)
  rises together with the section after it, so the statement never shows through.
- **"Shaped by people."**: soft fade at the left/right edges instead of a hard cut, slightly smaller, white-to-peach
  gradient letters, orange dot, thin divider under it.

## v2.8
- **Hero (icon + heading)**: less empty space on top (min-height 86svh, top padding 104px). Still editable:
  Ink Hero > Style > Min height / Padding.
- **Video lands next to "Build"**: the scroll video now shrinks into a slot right of line 1 of NK Hero ("Build [video]"),
  with "clarity." on the line below. NK Hero > Visual > "Scroll video lands next to line 1" (on by default) + "Video size".
  Turn it off to get the floating images back (or to let NK Stats be the landing slot again).
- **Next section slides over the statement**: "Empower brands." / "Align what matters." stay pinned while the section below
  ("Work — what we build", "Insights") rises over them with rounded corners + shadow; the statement shrinks and darkens.
  NK Statement > "Next section slides over" (on/off, background colour, corner radius, shrink amount). Live site only.
- **Footer**: empty 3D-logo spot removed (the icon no longer returns after the video), giant wordmark fits the width,
  tighter spacing, cleaner bottom bar and mobile layout.

## v2.7
- **Video flies down**: the scroll video (Vimeo) scales in, then as you keep scrolling the same playing video shrinks and lands as a
  small player in the NK Stats section under "Build clarity" (above "The difference our work makes"). Switch per section:
  NK Stats > "Landing slot for the scroll video". If no slot exists the video just animates in place.
- **3D icon hides from the video section on** and never shows again (statement sections + footer included). Scroll back up and it returns in the hero.
  Pages without an NK Scroll Video are unchanged.

## v2.6
- **Hover swing**: move the mouse left / right (no click needed) and the whole environment — 3D logo, glass reflections, lights,
  vignette, grain — swings toward that side, then eases back. Scrolling still moves the icon up, down, further down, then along its path.
- **Hero icon position** nudged sideways so it sits fully in frame. Tweak in `assets/js/ink-hero-3d.js`: `HERO_X` / `HERO_Y`.
- **NK Scroll Video** widget (Elementor > NK Studio): Vimeo player URL (or .mp4) in a rounded card that scales up, rises and
  un-tilts while you scroll. Default = your Vimeo link. Added to Home right after the first logo strip (one-time, existing sites too).
- **Header** is now exactly: brand left, glass pill menu, black Get Started. No bar behind it on scroll, no MENU button on desktop
  (MENU button only on small screens).


## v2.5 — footer redesign
- Top: big headline (Customize > NK Footer > headline) with the muted line as a readable sub-text, white CTA + glass email button,
  newsletter in a glass card with an orange submit button.
- Middle: 3D logo landing spot + INKNALGORITHM® / Studio side by side, footer paragraph under it; 5 menu columns
  (small spaced headings, links slide right with an orange dot on hover).
- Bottom bar: copyright, memberships, round social icons, language, back-to-top button.
- Giant faded INKNALGORITHM wordmark at the very bottom (letter-by-letter reveal).

## v2.4
- Dark sections back to their original tint (extra black removed). The 3D logo no longer shrinks there:
  it keeps moving on its path behind the section and is simply not visible while the dark section covers it,
  then appears again (same path) as soon as a plain section comes on screen. Footer landing unchanged.
- Brand text INKNALGORITHM in italic (header + footer). DM Sans italic 500/600 is loaded.
- Hero scroll: the icon goes up once, comes back down, then goes further down, then continues its normal path.
- Click + drag (mouse) anywhere that isn't a link/button/field: the whole environment swings left/right with the drag
  (camera, glass reflections, lights, vignette, grain) and springs back when you let go. Touch scrolling is untouched.

## v2.3
- **Header** smaller and cleaner: compact glass menu, smaller Get Started button, brand INKNALGORITHM with wide letter-spacing.
- **Social proof** premium: round ringed avatars in a glass pill, "10k+" bubble, 5 stars (Ink Hero > Avatars: bubble text + stars switch).
- **Black-layer (dark) sections**: the 3D logo now sinks away into the environment as a dark section comes in, instead of being cut by its edge.
  (v2.4: replaced — see above.) Any Elementor section/container with CSS class `ink3d-hide` also hides the logo behind it.
- **Footer landing**: at the end of the page the environment turns espresso and the 3D logo shrinks, spins once and lands
  on the footer mark (above INKNALGORITHM® / Studio), then stays there gently swaying.
  Caption under the brand: Customize > NK Footer > Footer mark caption.
- **Text animation**: hero headline + footer brand + big words reveal letter-by-letter (rise, unblur, 3D flip);
  section headlines word-by-word with blur + tilt; paragraphs line-by-line with blur.
- Optional model `assets/models/ink-algorithm-sculpture.glb` included (Ink Hero > 3D model > model URL to use it).


## v2.2
- "Empower brands." / "Align what matters." now show the NEW environment everywhere — Elementor editor and live site
  look the same: orange gradient, no dark tint, no old orange slash/ladder, 3D glass logo behind the text.
  The look is pure CSS, so it holds even if the 3D script is blocked or cached.
  NK Statement > 3D / Background > **Environment**: Ink (new, default) or Classic (old look) per section.
- 3D scene also loads on pages that only have NK Statement sections (no Ink Hero needed).
- On update, Elementor CSS + element cache is cleared once so the live site picks up the change.
- **HTML tags editable** on every headline: Ink Hero, NK Hero, Statement (+ text tag), Section Heading, Text Block,
  Work Grid (+ card title tag), Insights, Testimonials, Studio — H1–H6 / div / p / span.
- Brand text: **INKNALGORITHM** (one word, capitals). Customize > NK General > Text logo + "CAPITAL letters" checkbox.
  Old saved value "Ink Algorithm" is upgraded automatically.


## v2.1
- Statement sections ("Empower brands.", "Align what matters.") use the same environment as the hero: orange gradient,
  and the glass icon glides to the centre behind the text and spins while the section is on screen
  (old dark tint / particles / slash are hidden while the 3D logo is on the page).
  Any other element with the attribute `data-ink3d-stop` becomes a stop too.
- Fixed paragraph line-reveal breaking lines in odd places.

## What changed in v2.0
- **Ink Hero (3D glass)** Elementor widget: headline, social proof, avatars, description and 2 buttons are all editable.
  The 3D glass INKNALGORITHM logo is fixed behind the page and turns while you scroll (three.js r170 from jsDelivr,
  loaded only on pages that use this widget). Model file: `assets/models/ink-algorithm-mark.glb`.
- **Header** in the Ink style: text brand ("INKNALGORITHM", Customize > NK General > Text logo), glass menu
  (Primary menu), black CTA button (Customize > NK Header). On small screens: MENU button > overlay.
- **Environment**: orange gradient + film grain behind every page. Light/grey sections are transparent over it,
  dark sections get an espresso tint. Section layouts are unchanged.
- **Accent colour** is orange `#ff6a1a` (was green). Customize > NK General > Accent colour still works.
- On update, the Home page gets the Ink Hero automatically once (it replaces the first old "NK Hero" section).


Requires: WordPress 6+, Elementor (free is enough), PHP 7.4+.

## Install
Appearance > Themes > Add New > Upload `nk-studio.zip` > Activate.
On activation it creates the demo pages (Home, Work, Services, …), menus, sample projects/posts and sets the front page.

## Where things are edited
| What | Where |
|---|---|
| Logo image | Customize > Site Identity (or text logo in NK General) |
| Accent colour | Customize > NK General |
| Header CTA / MENU label | Customize > NK Header |
| Header menu | Appearance > Menus > location **Primary** |
| Footer text, newsletter, socials, memberships | Customize > NK Footer |
| Footer link columns | Menus assigned to **Footer column 1–5** (menu name = column title) |
| Any page section | Edit with Elementor > widget group **NK Studio** |
| Projects | Dashboard > Work: title, excerpt, featured image, badge, sector, external link |
| Reviews | Dashboard > Reviews: name, review, photo, company, logo, stars |
| Social videos | Dashboard > Social Videos: video link or MP4 + cover |
| Insights | normal Posts |

## Components (Elementor widgets) — reusable on any page
NK Hero · NK Logo Strip · NK Stats Marquee · NK Statement (3D background) · NK Work Grid · NK Insights · NK Testimonials · NK Studio/Locations · NK Section Heading · NK Arrow Link · NK Text Block · NK Saved Section (any saved template / Home section).
Each has Appearance controls: light/grey/dark theme, colours, padding, min-height, headline typography.

## 3D background
NK Statement > "3D / Background": built-in animated particle landscape, image, video, or **Custom embed** (paste an `<iframe>` to a Three.js/Spline/etc. scene, or your own HTML/canvas).

## Add a new component
Create `inc/elementor/widgets/nk_yourname.php` (copy any widget, class extends `\NK_Studio\Base`) and register it in `inc/elementor/loader.php`.

## Re-import demo
Appearance > NK Studio Setup.
