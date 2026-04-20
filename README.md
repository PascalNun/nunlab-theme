# N:UN Lab Theme

Custom classic WordPress theme for the N:UN Lab website.

This repository contains the theme code, local development tooling, and the VPS deploy workflow. WordPress content itself is managed inside WordPress and is not synced from this repo.

## Project Direction

N:UN Lab is being built as:

- a long-scrolling homepage with a dark hero and curated section flow
- a WordPress-managed `Work` index driven by `Projects`
- a `Notebook` built on normal WordPress posts
- editorial homepage sections such as `About` and `Manifesto`
- a lightweight custom theme with minimal external dependencies

Current stack choices:

- classic PHP WordPress theme
- native theme JavaScript in `assets/js/theme.js`
- modular SCSS compiled to one theme stylesheet
- self-hosted fonts
- no Bootstrap or frontend framework

## Repository Structure

```text
NUNLab/
├── assets/
│   ├── css/                  # compiled CSS
│   ├── fonts/                # self-hosted Barlow / Barlow Condensed
│   ├── icons/                # search icon and similar UI assets
│   ├── images/brand/         # logo, favicon, brand assets
│   ├── js/                   # theme JS
│   ├── media/hero/           # hero video, poster, frame sequence
│   └── scss/                 # source SCSS
├── inc/
│   ├── enqueue.php
│   ├── hooks.php
│   ├── meta-boxes.php
│   ├── post-types.php
│   ├── template-tags.php
│   └── theme-setup.php
├── scripts/
│   ├── bootstrap-live-wordpress.php
│   ├── deploy-vps.sh
│   ├── seed-local-notebook-posts.php
│   ├── seed-local-projects.php
│   ├── dev-session.sh
│   └── wp-router.php
├── template-parts/
│   ├── content/
│   ├── home/
│   └── site/
├── .wp-local/                # local WP sandbox, git-ignored
├── front-page.php
├── home.php
├── archive-project.php
├── single-project.php
├── search.php
├── functions.php
└── style.css                 # WP theme header file
```

## Theme Content Model

### WordPress post types

- `page`:
  - `Home`
  - `About`
  - `Manifesto`
  - `Plugins`
  - `Contact`
  - `Imprint`
- `post`:
  - used for `Notebook`
- `project`:
  - used for portfolio / work entries

### Project grouping

Projects are grouped with the `project_type` taxonomy. Default terms are seeded:

- `Concept`
- `Work`
- `Research`
- `Build`

The homepage `Work` section is taxonomy-driven. Non-empty project types can appear there automatically.

### Homepage source content

The static front page uses editable meta fields and source pages:

- `Hero Title`
- `Hero Intro`
- `Work Eyebrow`
- `Work Heading`
- `About Section Source Page`
- `Manifesto Section Source Page`

`About` and `Manifesto` content are edited on their own WordPress pages and rendered into the scrolling homepage.

### Project media

Projects support a `Project Media` meta box in WordPress admin.

Supported slide types:

- image
- YouTube video

That media sequence powers:

- homepage project expansion
- single project pages
- project previews/search previews where relevant

## Local Development

### Requirements

- PHP CLI
- Node.js / npm
- WordPress local sandbox in `.wp-local/`

The repo already includes project-local Sass via `npm`.

### Install

```bash
npm install
```

### Useful commands

```bash
npm run sass:build
npm run sass:watch
npm run lint:php
npm run wp:serve
npm run wp:url
```

### Local sandbox

- local WordPress lives in `.wp-local/`
- local URL: `http://127.0.0.1:8080`

Local demo content can be seeded when needed:

```bash
wp --path=.wp-local eval-file scripts/seed-local-projects.php
php -d error_reporting=24575 /opt/homebrew/bin/wp --path=.wp-local eval-file scripts/seed-local-notebook-posts.php
```

### VS Code

This repo includes a simple Run and Debug setup:

- open the repo in VS Code
- select `NUNLab`
- press `F5`

That starts the local dev session from `scripts/dev-session.sh`.

## Live VPS Deploy

The theme is deployed to the VPS by SSH and `rsync`. WordPress content is not overwritten by deploys.

### Local-only server config

Create a local `.env.local` file with:

```bash
NUNLAB_VPS_HOST=...
NUNLAB_VPS_USER=...
NUNLAB_VPS_ROOT_PASSWORD=...
NUNLAB_VPS_WP_ROOT=...
```

`.env.local` is git-ignored and should never be committed.

### Deploy commands

Full deploy:

```bash
npm run deploy:vps
```

Theme sync only:

```bash
./scripts/deploy-vps.sh --sync-only
```

### What full deploy does

`scripts/bootstrap-live-wordpress.php` currently:

- activates the theme
- ensures the main site pages exist
- sets the static front page
- sets the posts page to `Notebook`
- sets homepage source-page relationships
- creates/assigns the primary menu
- creates/assigns the legal footer menu
- removes default sample content

It does not seed fake portfolio or notebook content to live.

## Design and Asset Notes

### Header assets

Current header assets live here:

- `assets/images/brand/`
- `assets/icons/`

The header switches between dark/light SVG assets automatically based on theme state.

### Favicons

Preferred setup:

- `assets/images/brand/favicon.svg`
- `assets/images/brand/favicon-512.png`

The theme outputs the SVG first and the PNG as fallback when no WordPress `Site Icon` is set.

### Hero media

Hero assets live in `assets/media/hero/`.

Current setup supports:

- scrub-optimized MP4
- additional WebM fallbacks
- poster images
- frame sequence fallback / sequence mode for browsers where scrubbing is more reliable that way

## Styling Architecture

SCSS is intentionally kept modular but not overbuilt.

Current structure:

- `base/`
- `layouts/`
- `components/`
- `pages/`
- `sections/`

The main entry file is:

- `assets/scss/style.scss`

Compiled output:

- `assets/css/style.css`

## WordPress Editing Guide

### Edit the scrolling homepage

In WordPress admin:

1. open `Pages > Home`
2. edit the `Front Page Content` meta box
3. edit the source pages for `About` and `Manifesto`

### Edit portfolio content

In WordPress admin:

1. open `Projects`
2. edit title, excerpt, content, featured image, order
3. assign a `Project Type`
4. manage slideshow/media in `Project Media`

### Edit notebook content

In WordPress admin:

1. open `Posts`
2. create/edit notebook entries normally

### Edit footer legal link

In WordPress admin:

1. open `Appearance > Menus`
2. edit the `Legal Footer Menu`

## Browser Notes

Modern Chrome, Safari, Firefox, and Edge are supported.

Some hero behavior is browser-specific:

- desktop browsers prefer direct video scrubbing when reliable
- iPhone / some browsers may use the frame-sequence path for smoother scroll behavior

## Working Principle

Theme code lives in Git.

Content lives in WordPress.

That means:

- sync theme/layout/interaction changes from this repo
- enter real projects, notebook posts, and editorial text inside WordPress
- do not treat this repo as the source of live editorial content
