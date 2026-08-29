# AGENTS.md

## Stack & setup

- Plain **PHP 7.4+** (procedural, no Composer, no framework, no build step) + **MySQL 8** via PDO. UI is Tailwind CSS v3 + Lucide loaded **from CDNs per page** (`https://cdn.tailwindcss.com`, unpkg lucide) — needs internet at runtime; after editing HTML you still call `lucide.createIcons()`.
- Serve the app with any PHP web server pointing at `src/pages` (docroot); pages are direct `.php` entrypoints (no front controller). E.g. `php -S localhost:8000 -t src/pages`.
- All cross-page links/redirects are bare filenames (same dir) and relative `require`/`include` use `../` to reach `src/config` and `src/components`.

## How the app is wired

- Each `src/pages/*.php` is both controller and view: PHP does auth + queries at the top, then prints HTML.
- `src/config/database.php` exports class `Database`; `getConnection()` returns a PDO handle. DB `sports_management`, creds **hardcoded** in this file.
- `src/config/session.php` defines auth helpers `isLoggedIn()`, `requireLogin()`, `getUserType()`, `requireUserType()`, `getCurrentUser()`. Pages include it via `require_once '../config/session.php'`, then gate by role.
- Role gating is **inconsistent**: some pages call `requireUserType([...])`, others hand-check `getUserType() !== 'x'` and redirect to `index.php` (e.g. src/pages/attendance.php:6). `requireUserType()` redirects to `unauthorized.php`, which **does not exist in the repo** — don't rely on that path, and match the existing per-file style when editing.
- `src/components/header.php` and `src/components/sidebar.php` render role-based layout; pages `include` them.
- No CSRF tokens anywhere (documented as known-pending in `docs/recomendaciones-y-errores.md`). Keep using prepared statements + `htmlspecialchars()` for all output.

## Database

- Setup: run `database/schema.sql` then `database/seed_data.sql` against a fresh MySQL. No migration framework; SQL is applied manually (`database/migrations/`).
- Seed users use bcrypt `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` = `password`.
- Role values are Spanish: `user_type` ENUM `coordinador | entrenador | estudiante`. But `attendance.status` is English `present | absent | late` and `student_enrollments.status` is `activo | inactivo`. Match column values exactly; don't translate in queries.

## Repo quirks

- Repo layout: pages in `src/pages/`, reusable PHP in `src/config` + `src/components`, SQL in `database/`, docs (Spanish) in `docs/`, JMeter plans in `tests/jmeter/`.
- A `.env` was deleted on purpose — the app does **not** load `.env` (src/pages/login.php comment is stale); credentials stay in `src/config/database.php`. `.gitignore` now ignores env files and `*.log`.
- `src/styles/globals.css` is not referenced by any PHP page (CDN Tailwind instead).
- `docs/estructura-carpetas.md` used to mention a `public/` dir that didn't exist; `docs/recomendaciones-y-errores.md` describes refactors/tests (MVC, PHPUnit, env vars) that are **not implemented**. Trust the code over the docs.
- All UI text, DB values, and docs are Spanish — keep new user-facing strings in Spanish.

## Testing

- No automated tests. Performance testing is done with Apache JMeter (`tests/jmeter/login_test_plan_corrected.jmx`). Verify changes by exercising the pages manually in a browser.