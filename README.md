# N:UN WordPress Theme

Custom classic WordPress theme behind `pascalnun.eu`.

N:UN is an architectural portfolio, notebook, and tools platform. The theme is built to hold work, writing, research, and small software projects in one coherent editorial frame: quiet where the content needs room, precise where interaction matters, and light enough to remain understandable.

This is a site-specific theme rather than a generic starter theme. Still, the repository is meant to be readable from the outside: a small example of how a portfolio site can stay close to WordPress, keep content editable, and use custom code only where the design asks for it.

WordPress owns the authored material. This repository owns the structure, presentation, interaction layer, local tooling, and deployment workflow.

## Design and Code Ethos

The theme follows a simple technical attitude: use the smallest system that can carry the work properly.

The codebase favors:

- classic WordPress templates over a frontend application stack
- semantic PHP templates and reusable helper functions
- CSS for layout, rhythm, responsiveness, and visual states wherever possible
- JavaScript where interaction genuinely needs state, timing, media control, or keyboard/touch behavior
- self-hosted assets and fonts
- human-readable comments where they clarify structure or intent
- content that remains editable in WordPress rather than being locked into templates

The design language is architectural rather than decorative. Layouts are allowed to be spacious, but they should serve reading, comparison, sequence, and orientation. Cards, shadows, media, and motion are used as framing devices, not as surface effects.

The code tries to keep a similar discipline. Helpers are allowed to be substantial when they hold real rendering logic, but abstraction should earn its place. A function, class name, or comment should make the project easier to read six months later.

For historical continuity, some implementation names still use `nunlab`: the text domain, PHP function prefix, theme folder, and deployment paths. The public brand is `N:UN`.

## Site Structure

The site is organized around four main content modes:

- `Home`: a long-scrolling front page with a dark media hero and curated sections
- `Work`: architectural and design projects powered by the `project` post type
- `Notebook`: essays, notes, and fragments powered by normal WordPress posts
- `Plugins`: tools and Grasshopper/Rhino plugin pages powered by the `tool` post type

Supporting pages include `About`, `Contact`, `Manifesto`, and `Legal Notice`.

## Technical Stack

- classic PHP WordPress theme
- vanilla JavaScript in `assets/js/theme.js`
- modular SCSS compiled to `assets/css/style.css`
- WordPress block editor for authored content
- self-hosted Barlow / Barlow Condensed fonts
- private server-log analytics dashboard in WordPress admin
- no Bootstrap, React, Vue, or page-builder dependency

## Public Readability

The repository is organized so that a reader can understand the site without needing the live database:

- post types and editable fields are registered in `inc/`
- templates and partials describe the front-end structure
- SCSS carries most of the visual system
- JavaScript enhances already-rendered HTML
- scripts document local development, deployment, and server-log analytics workflows

Live editorial content, uploaded media, SMTP secrets, and server settings are intentionally outside the repository.

## Repository Structure

```text
NunLAB/
├── assets/
│   ├── css/                  # compiled front-end CSS
│   ├── fonts/                # self-hosted fonts
│   ├── icons/                # UI icons
│   ├── images/
│   │   ├── brand/            # logo and favicon assets
│   │   └── tools/            # plugin/tool imagery
│   ├── js/                   # theme behavior
│   ├── media/hero/           # hero video, posters, sequence frames
│   └── scss/                 # source styles
├── inc/
│   ├── enqueue.php           # asset loading
│   ├── hooks.php             # filters, search, favicons, image quality
│   ├── meta-boxes.php        # editable project/front-page/tool fields
│   ├── post-types.php        # project, tool, and project_type registration
│   ├── admin-analytics.php   # private WP admin analytics dashboard
│   ├── template-tags.php     # rendering and media helpers
│   └── theme-setup.php       # theme supports and menus
├── scripts/
│   ├── analytics/
│   │   └── generate-summary.py
│   ├── bootstrap-live-wordpress.php
│   ├── deploy-vps.sh
│   ├── setup-vps-analytics.sh
│   ├── dev-session.sh
│   ├── dev-up.sh
│   ├── dev-down.sh
│   ├── seed-local-notebook-posts.php
│   ├── seed-local-projects.php
│   └── wp-router.php
├── template-parts/
│   ├── content/
│   ├── home/
│   └── site/
├── front-page.php            # scrolling homepage
├── home.php                  # notebook index
├── page-plugins.php          # plugins index
├── archive-project.php       # work archive
├── single-project.php
├── single-tool.php
├── search.php
├── functions.php
└── style.css                 # WordPress theme header
```

## Content Model

### Pages

Normal WordPress pages hold stable editorial surfaces:

- `Home`
- `About`
- `Manifesto`
- `Plugins`
- `Contact`
- `Legal Notice`

The `Plugins` page uses the `Plugins Index` template and queries published `tool` entries.

### Notebook

Notebook entries are normal WordPress posts. This keeps writing close to the default WordPress editorial flow while allowing the theme to give single posts a more deliberate reading layout.

### Projects

The `project` custom post type holds portfolio/work entries.

Projects support:

- title, excerpt, editor content, featured image, and order
- optional two-line presentation titles
- `project_type` taxonomy
- a `Project Media` sequence with images and YouTube slides

The same project data is used across homepage work cards, expanded project states, archive views, single project pages, and search results.

Default `project_type` terms:

- `Concept`
- `Work`
- `Research`
- `Build`

### Plugins / Tools

The internal post type is `tool`; the public language is `Plugins`.

Tool entries support:

- title and excerpt
- featured image or poster
- optional plugin icon URL
- optional YouTube walkthrough URL
- external links for GitHub, Food4Rhino, documentation, and release/download
- editor content structured into overview and chapter-like sections

The first intended plugin entry is `RhinoSpatial`:

```text
https://github.com/PascalNun/RhinoSpatial
```

## Styling Approach

SCSS source:

```text
assets/scss/style.scss
```

Compiled output:

```text
assets/css/style.css
```

Build CSS:

```bash
npm run sass:build
```

The SCSS is organized into:

- `base/`
- `layouts/`
- `components/`
- `pages/`
- `sections/`

The styling system is intentionally modest. It uses shared variables and component files where they help, but avoids abstraction for its own sake. Responsive behavior is mostly handled in CSS through grids, columns, clamps, and content-aware constraints.

Editorial text layouts are a central part of the theme. The project uses multi-column reading patterns in selected places, but keeps the content model simple enough that text can still be edited naturally in WordPress.

## JavaScript Approach

Main front-end behavior:

```text
assets/js/theme.js
```

JavaScript is used for:

- search overlay
- mobile navigation
- sticky/header state
- hero media behavior
- section-aware navigation
- homepage project expansion
- project media galleries
- keyboard and touch interactions

The theme keeps JavaScript as a behavior layer rather than the primary rendering model. Templates render meaningful HTML first; JavaScript enhances interaction afterwards.

## Local Development

Requirements:

- PHP CLI
- Node.js / npm
- project-local WordPress sandbox in `.wp-local/`
- optional WP-CLI for content seeding

Install dependencies:

```bash
npm install
```

Useful commands:

```bash
npm run sass:build
npm run sass:watch
npm run lint:php
npm run wp:serve
npm run wp:url
```

Local WordPress runs at:

```text
http://127.0.0.1:8080
```

Seed local demo content:

```bash
wp --path=.wp-local eval-file scripts/seed-local-projects.php
php -d error_reporting=24575 /opt/homebrew/bin/wp --path=.wp-local eval-file scripts/seed-local-notebook-posts.php
```

## WordPress Editing

### Homepage

1. Open `Pages > Home`
2. Edit the `Front Page Content` meta box
3. Edit source pages for `About` and `Manifesto`

### Work / Projects

1. Open `Projects`
2. Edit title, excerpt, content, featured image, and order
3. Assign a `Project Type`
4. Manage image/video sequences in `Project Media`

### Notebook

1. Open `Posts`
2. Create or edit notebook entries normally

### Plugins

1. Open `Site Plugins`
2. Create or edit plugin entries
3. Add links and walkthrough data in `Plugin Details`
4. Use editor headings and content for overview/chapter sections

### Legal Footer

1. Open `Appearance > Menus`
2. Edit the `Legal Footer Menu`

### Analytics

The private WordPress dashboard includes `Dashboard > N:UN Analytics`.

It reads a generated JSON summary from the server and does not add a public analytics script to the site. The dashboard is intended as a lightweight signal layer for a small portfolio/tool site, not as a Google Analytics replacement.

The main metrics distinguish between:

- broad page views
- approximate unique IPs
- stricter likely visits
- content views on non-homepage pages
- filtered bot/scanner/noise requests

## Live Deployment

The deploy scripts reflect the workflow for `pascalnun.eu`. They sync theme code to the VPS over SSH and `rsync`; the WordPress database and uploaded media remain live-site state.

Local server configuration belongs in `.env.local`, which is ignored by Git. The exact variables are read by `scripts/deploy-vps.sh`.

Expected local variables include:

```bash
NUNLAB_VPS_HOST="example-host-or-ip"
NUNLAB_VPS_USER="nun"
NUNLAB_VPS_SSH_KEY="$HOME/.ssh/nunlab_vps"
NUNLAB_VPS_WP_ROOT="/var/www/wordpress"
```

The current live workflow uses key-based SSH through a non-root deploy user. Password-based SSH and root SSH login are disabled on the VPS. Personal SSH instructions belong in `.local/`, which is ignored by Git.

Full deploy:

```bash
npm run deploy:vps
```

Theme-only sync:

```bash
./scripts/deploy-vps.sh --sync-only
```

`scripts/bootstrap-live-wordpress.php` ensures the basic live WordPress structure exists: main pages, menus, homepage settings, and base options. Authored portfolio, notebook, plugin, and legal content is managed directly in WordPress.

## Server-Side Services

### Outgoing Mail

WordPress outgoing mail on the live VPS is configured outside the theme through SMTP.

The live setup uses:

- SMTP constants in live `wp-config.php`
- a must-use plugin at `wp-content/mu-plugins/nunlab-smtp.php`
- no SMTP secrets in this repository

### Image Handling

The theme registers a large project image size and applies higher WordPress image quality settings. Uploaded media remains in WordPress uploads.

### Local Log Analytics

The analytics setup is server-side and privacy-light:

- nginx access logs are summarized locally on the VPS
- the generator writes aggregate JSON to `/var/lib/nunlab-analytics/summary.json`
- WordPress reads that JSON in a private admin page
- no visitor-side analytics JavaScript is loaded
- no visitor IP addresses are stored in WordPress

Provision or refresh the VPS analytics generator:

```bash
./scripts/setup-vps-analytics.sh
```

The summarizer lives at:

```text
scripts/analytics/generate-summary.py
```

## Browser Notes

Modern Chrome, Safari, Firefox, and Edge are supported.

Hero behavior varies by browser and device:

- desktop browsers use direct video scrubbing where reliable
- Firefox and reduced-motion contexts can use sequence/fallback paths
- iPhone and some device/browser combinations may use frame sequences for smoother scroll playback

## Local Files

Expected local-only files:

- `.env.local`
- `.local/`
- `.wp-local/`
- `content-drafts/`
- `node_modules/`
- `.vscode/`
- OS files such as `.DS_Store`

Useful status check:

```bash
git status --short --ignored
```

## Working Principle

Theme code lives in Git.

Content lives in WordPress.

The repository should stay lean enough to understand directly. The live site can grow through authored work, writing, images, and plugin documentation without forcing the theme into a heavier system than the project needs.


## License

Theme code is licensed under GPL v2 or later, following WordPress theme conventions. See `LICENSE`.

Bundled Barlow font files are licensed under the SIL Open Font License 1.1. See `assets/fonts/OFL.txt`.

Additional notes for fonts, development dependencies, and site-specific assets are collected in `THIRD_PARTY_NOTICES.md`.
