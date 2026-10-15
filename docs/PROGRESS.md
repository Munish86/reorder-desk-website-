# Reorder Desk website: progress and handoff

Read this first when starting a new session. Last updated: 2026-10-15 (built locally).
Technical overview: `../README.md`. Owner's guide (Hinglish): `OWNER-GUIDE.md`.

## Goal

A professional marketing website for the Reorder Desk Shopify app (`../../restock-app/`), in WordPress so the owner can
edit pages and write blog posts. Built locally on XAMPP first; moved to real hosting once the name and domain are settled.

## Decisions

| Topic | Decision |
|---|---|
| Platform | WordPress 7.1.3, own block theme (no page builder, no paid theme) + small own plugin |
| Local URL | http://localhost:8088 via an Apache vhost on port 8088 (root install, so root-relative links survive the move) |
| Database | `reorderdesk_wp`, XAMPP `root`. A `reorderdesk` MySQL user was created but has no grants (see below) |
| Source of truth | Theme and plugin live in this folder; WordPress has junctions to them. Page/post bodies in `content/` |
| CTA | Every main button has class `rd-cta` -> /early-access/; Settings > Reorder Desk site > App Store URL switches them all to "Install free on Shopify" |
| Forms | Own plugin (no Contact Form 7): saved in `{prefix}rd_leads`, Leads admin page + CSV; honeypot, signed time stamp, 6/hour per IP (hashed) |
| SEO | Yoast SEO (titles, descriptions, sitemap, OG image); plugin adds SoftwareApplication schema on the home page |
| Privacy | No cookies for visitors, no analytics, no third-party requests (font self-hosted) |
| Copy | English, facts taken from restock-app code (forecast.ts, settings.ts, plans.ts) and docs/LISTING.md |

## State on 2026-10-15

- Pages: home, features, how-it-works, pricing, stocky-alternative, help, early-access, contact, privacy-policy, terms,
  blog (posts page). Posts: 4 guides with generated covers.
- `tools/shots.mjs`: all pages 200 at 1440px and 390px, no console errors, no horizontal overflow.
- Fixed after the first screenshots: hero mock-up table clipped, eyebrow labels not centred/left in constrained groups,
  pricing card text centred, form "(optional)" on its own line.

## Open points for the owner

1. **Name conflict:** reorderdesk.com is registered and runs "Reorder Desk — Outsourced demand planning" (weekly
   PO-ready reorder plans for Shopify inventory brands), found 2026-10-15. Same niche, same name. Owner to decide
   whether to rename the app before launch. Renaming the site = search/replace in content/, theme, plugin, covers.
2. Pro price $29 and 14-day trial (also in restock-app `plans.ts` / `docs/LISTING.md`) are not confirmed.
3. Promises to confirm: "reply within one working day" (contact), early-access emails deleted within 12 months of launch
   (privacy), governing law and legal entity name (terms/privacy).
4. Domain + hosting, then move with All-in-One WP Migration (steps in OWNER-GUIDE.md section 7).
5. XAMPP's `mysql.db` privilege table is marked crashed (CHECK TABLE: "Corrupt"). Claude was not allowed to repair it.
   It only matters for non-root MySQL users. Fix: `REPAIR TABLE mysql.db;` in phpMyAdmin (back up
   `C:\xampp\mysql\data\mysql\db.*` first).
6. OPcache is off in XAMPP, so local pages take 5-20 s. Owner can enable it (OWNER-GUIDE.md section 1).

## Gotchas

- Git Bash rewrites arguments starting with `/` into Windows paths (`/%postname%/` became
  `/C:/Program Files/Git/%postname%/` once and broke every post URL). `tools/wp.sh` sets `MSYS_NO_PATHCONV=1` itself;
  for `shots.mjs` pass paths without the leading slash (`pricing/`).
- Chrome headless hangs on this PC; the scripts use Edge first.
- WP-CLI's own sub-processes break on the space in the project path (`rewrite structure` printed "Could not open input
  file"); run `tools/wp.sh rewrite flush` separately.
- XAMPP PHP prints `Module "ftp" is already loaded` on every run; `tools/wp.sh` filters it, and `php -r` output must be
  run with `-d display_startup_errors=0` or the warning ends up in captured values.
- Page markup is saved through WP-CLI with `--user=reorderadmin`, otherwise kses strips attributes.
- `setup-content.php force` overwrites pages edited in WordPress. Once the owner starts editing, change pages in
  WordPress (or update the files in content/ from WordPress first).
