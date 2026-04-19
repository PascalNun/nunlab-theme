# N:UN Lab WordPress Theme

A minimal, architectural custom WordPress theme for the N:UN Lab platform.

## Phase 1: Foundation

### What's Included
- Clean WordPress theme structure with proper file organization
- Homepage template for long-scrolling landing page experience
- Template hierarchy for blog, projects, and custom pages
- SCSS-ready styling system with variables and base utilities
- Responsive design with mobile-first approach
- Proper theme setup with WordPress hooks and best practices

### File Structure
```
nunlab-theme/
├── assets/
│   ├── css/              # Compiled stylesheets
│   ├── scss/             # SCSS source files with modular structure
│   └── js/               # Theme JavaScript
├── inc/                  # Theme logic, post types, and template helpers
├── template-parts/       # Reusable site, content, and homepage sections
├── front-page.php        # Curated long-scrolling homepage
├── home.php              # Notebook / writing index
├── archive-project.php   # Project archive
├── single-project.php    # Project case study
├── functions.php         # Main theme setup file
├── header.php            # Global header wrapper
├── footer.php            # Global footer wrapper
├── index.php             # Fallback template
└── style.css             # Theme header file
```

### Getting Started

1. **Theme Installation**
   - Copy this folder to `wp-content/themes/`
   - Activate the theme in WordPress admin

2. **SCSS Compilation** (Optional but recommended)
   - Set up a build tool like `dart-sass` or `node-sass`
   - Compile `assets/scss/style.scss` to `assets/css/style.css`
   - For development: `sass --watch assets/scss:assets/css`

3. **Configure Site Settings**
   - Set a site title and tagline in WordPress
   - Create a homepage and assign it as static front page
   - Set up navigation menus under Appearance > Menus

### Local Tooling

- `php` is used for syntax checks and WordPress tooling
- `composer` is available for future PHP dependencies
- `wp-cli` is available for local WordPress setup and maintenance
- `mariadb` provides the local database server
- `npm install` adds a project-local Sass compiler

Useful commands:

- `npm run sass:build`
- `npm run sass:watch`
- `npm run lint:php`
- `npm run wp:serve`
- `npm run wp:url`

Local sandbox:

- WordPress core is installed in `.wp-local/` and ignored by git
- Local URL: `http://127.0.0.1:8080`
- Admin user: `nunlab_admin`
- Admin password: `nunlab_local_admin`

### Next Steps for Phase 2

- [ ] Implement design system (typography scales, colors, spacing)
- [ ] Refine homepage section content and art direction from Figma
- [ ] Build notebook archive and single post styles
- [ ] Develop project/case study page templates
- [ ] Add motion and scroll animations
- [ ] Optimize media and performance

### Theme Features

- ✓ Custom post type registration for projects
- ✓ Theme support for custom logo, featured images, HTML5
- ✓ Navigation menu management
- ✓ Sticky header
- ✓ Responsive design foundation
- ✓ Modular SCSS architecture

### Browser Support

Modern browsers (Chrome, Firefox, Safari, Edge). No IE11 support.

### License

GPL v2 or later
