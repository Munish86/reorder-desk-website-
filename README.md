# Reorder Desk website (WordPress)

Marketing website for the Reorder Desk Shopify app (`../restock-app/`): home, features, how forecasts work, pricing,
Stocky alternative, help centre, early access, contact, privacy, terms and a blog with four posts.
Owner's step-by-step guide (Hinglish): `docs/OWNER-GUIDE.md`. State and decisions: `docs/PROGRESS.md`.

## Local site

| | |
|---|---|
| Site | http://localhost:8088 (Apache vhost added to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`) |
| Admin | http://localhost:8088/wp-admin/ (login in `local-login.txt`, not for sharing) |
| WordPress | 7.1.3 in `C:\xampp\htdocs\reorderdesk` |
| Database | `reorderdesk_wp` on XAMPP MariaDB, user `root` (local only) |

The theme and plugin folders in WordPress are **junctions** pointing at this folder, so editing files here changes the
site at once:

- `C:\xampp\htdocs\reorderdesk\wp-content\themes\reorder-desk` -> `theme/reorder-desk`
- `C:\xampp\htdocs\reorderdesk\wp-content\plugins\reorder-desk-site` -> `plugin/reorder-desk-site`

## What is here

```
theme/reorder-desk/      Block theme. Tokens in theme.json, components (rd-*) in assets/css/site.css.
  templates/             front-page, page, page-landing (full width, no title), single, home (blog), archive, search, 404
  parts/                 header, footer
  patterns/              cta-band, feature-cards, faq, screenshot-split (Editor > Patterns > Reorder Desk)
  assets/images/         app screenshots (cropped from restock-app browser tests), logo, favicon, og-image
plugin/reorder-desk-site/
  includes/leads.php     [rd_waitlist], [rd_contact], [rd_support_email]; table {prefix}rd_leads
  includes/admin.php     Leads page (list, CSV, delete)
  includes/settings.php  Settings > Reorder Desk site (App Store URL, live label, support email, notify email)
  includes/cta.php       Launch switch: buttons with class rd-cta link to the App Store URL once it is set
  includes/meta.php      SoftwareApplication schema on the home page; meta/OG fallback when no SEO plugin
content/                 Page and post bodies (block markup), site.php (titles, slugs, templates, meta), covers/
tools/
  wp.sh                  WP-CLI for the local site (XAMPP PHP)
  setup-content.php      content/ -> WordPress (only missing pages unless "force")
  make-images.php        draws blog covers and og-image.png (PHP GD + Windows Segoe UI)
  shots.mjs              screenshots at desktop and phone width + console/404/overflow checks
  check-editor.mjs       opens every page in the block editor and reports invalid blocks
```

## Commands (Git Bash, from this folder)

```
tools/wp.sh <any wp-cli command>                                    # e.g. tools/wp.sh plugin list
tools/wp.sh eval-file tools/setup-content.php --user=reorderadmin   # add missing pages/posts
tools/wp.sh eval-file tools/setup-content.php force only=pricing --user=reorderadmin   # overwrite one page from content/
php tools/make-images.php                                           # redraw covers and og-image
node tools/shots.mjs pricing/ blog/                                 # screenshots into tools/.shots/ (no args = home)
node tools/check-editor.mjs                                         # block validation in the editor
```

`force` overwrites edits made in the WordPress editor for those pages; without it, existing pages are kept.

## Conventions

- Links inside pages and theme files are root-relative (`/pricing/`), so the site must live at the root of a domain
  (or subdomain), not in a sub-folder. Images in pages point at the theme folder
  (`/wp-content/themes/reorder-desk/assets/images/...`).
- Main buttons carry the class `rd-cta`; never hard-code the App Store URL in pages, set it in
  Settings > Reorder Desk site instead.
- Headings with `rd-ic rd-ic--<name>` get an icon tile (names in `patterns/feature-cards.php`).
- No third-party requests: the font (Inter, OFL) is self-hosted, no analytics, no cookies for visitors.
- Facts on the site come from `../restock-app` (forecast maths in `app/lib/forecast.ts`, defaults in
  `app/lib/settings.ts`, plans in `app/lib/plans.ts`). Change the site when those change.
