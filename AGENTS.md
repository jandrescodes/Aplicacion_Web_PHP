# AGENTS.md

Custom PHP 8 MVC framework (no Laravel). Runs only under XAMPP:
Apache + MySQL at `http://localhost/Aplicacion_Web_PHP/public/`.

> Full architecture, conventions, and domain rules are in `CLAUDE.md` — read it first when making changes.

## Commands (run in project root)

- Start/stop server: `sudo /opt/lampp/lampp start` / `... stop`
- Tests: `composer test:unit` (only suite that exists — CI runs `vendor/bin/phpunit --testsuite Unit`)
- Regenerate autoload after adding classes: `composer dump-autoload`
- Import schema/seed: `mysql -u root -proot -h 127.0.0.1 your_db < database/schema.sql` (then `seeders.sql`)
- Schema changes: edit `database/schema.sql` **AND** apply via `ALTER TABLE` directly (no migration runner). Existing databases need `login_attempts` (see `schema.sql`)
- Server/PHP errors: `/opt/lampp/logs/error_log`; app logs: `storage/logs/app.log`
- PHPUnit is a Composer devDependency → must run through `vendor/bin`, not a global binary

## Architecture (read `CLAUDE.md` for the layer table)

- **No manual `require_once`** — Composer PSR-4: `App\`→`app/`, `Core\`→`core/`, `Config\`→`config/`, `Tests\`→`tests/`
- Request flow: `public/index.php` (dotenv → Monolog → Container → dispatch) → `routes/web.php` → controllers → UseCases → Services → Repositories (PDO)
- Controller POST pattern is rigid: CSRF check → build typed `*Request` DTO → `validate()` → UseCase → check `OperationResult->success`
- **Do not** inject `$_SESSION`/`$_POST` into UseCases/Services/Domain; HTTP concerns stay in controllers/Requests
- DI Container is **bootstrap-only** (`public/index.php`, `routes/web.php`); domain/application layers are wired by constructor, not the container

## Hard-won gotchas

- **DB table names have hyphens** — always quote in SQL: `` `tbl-empleados` ``, `` `tbl-puestos` ``, `` `tbl-usuarios` ``
- **No dark theme in this project** — keep inputs/cards light. CSS has two eras in one file (`public/css/style.css`): login(`#tarjeta` et al.) and Bootstrap dashboard blocks
- **Auth**: passwords bcrypt via `password_hash()`; `AuthService::authenticate()` auto-migrates legacy plaintext—do not add migration logic
- **Remember me**: cookie `{userId}:{plainToken}`, plain token never stored (sha256 in DB); rotation + sliding expiry are intentional—do not "fix". The token **is revoked** (`clearRememberToken`) on any password change (own profile or admin edit) and on logout; `findByIdWithRememberToken` must keep selecting `is_admin` or admins lose the role after a cookie login
- **Login throttle**: `LoginThrottleService` (table `login_attempts`) blocks after 5 failures per typed username or 20 per `REMOTE_ADDR` within 900 s, checked before verifying the password. Accepted trade-off: someone can lock a known username for the window. Don't read `X-Forwarded-For`; behind a TLS proxy the per-IP bucket collapses
- **Web exposure**: the repo lives inside the Apache docroot. Root `.htaccess` is `Require all denied` and `public/.htaccess` re-grants (`Require all granted`); `public/storage/uploads/.htaccess` disables PHP (re-included in `.gitignore`). Don't remove them; they depend on `AllowOverride` being enabled. Keep secrets, logs and SQL out of `public/`
- **Authorization**: positions (all 6 routes), users and audit require `requireAdmin()`; employees and profile need only login. An admin cannot delete their own account (`UserUseCase::deleteUser`)
- **DB connection errors** throw (logged by `public/index.php`, 500 page) — never `die()` with the PDO message
- **Login animation** in `public/js/main.js` loops continuously (`complete: randomValues`); guard with `prefers-reduced-motion`, don't convert to single-pass
- **Accessibility conventions apply repo-wide**: decorative icons get `aria-hidden="true"`, icon-only buttons/links get `aria-label` including the entity name (e.g. `aria-label="Editar a {name}"`), file inputs link helper text via `aria-describedby="{field}-help"`, `<th>` gets `scope="col"`, CDN `<script>`/`<link>` tags need `integrity`+`crossorigin`+`defer`, admin/CRUD forms use `needs-validation` + `novalidate` (submit handler lives once in `main.js`). See `CLAUDE.md` → _Frontend_ → _Accessibility conventions_. Don't set `style="visibility: hidden;"` inline on `#tabla_id` — `main.js` does it via JS right before DataTables init
- **Views**: `variables` like `$nombreUsuario`, `$flash` come from `extract($data)` in `Core\View::render()` — those "undefined" IDE warnings are false positives
- PDFs: dompdf only; embed CSS via `<style><?= file_get_contents('public/css/x.css'); ?></style>` (link tags unreliable)
- Files upload to `public/storage/uploads/` with a random name (`bin2hex(random_bytes(16))` + validated extension); never reuse the client's filename (`x.php.jpg` runs as PHP under XAMPP's `AddType`). Never `deleteFileIfExists()` on `user-default.jpg` / `cv_default.pdf` (guarded in `EmployeeService`); `deleteFileIfExists` rejects `..` and requires the `realpath` inside the uploads dir

## Undocumented details worth knowing

- DataTables init lives in `public/js/main.js` (reads `data-module` on `#tabla`); module JS files only hold AJAX delete — don't move the init
- DataTables assets are **conditionally loaded**: controllers pass `['needDataTables' => true]` to `renderWithLayout()` in `index()` (Employees, Positions, Users, Audit); default is `false` in `Core\View`. New listing views must opt in, and don't set the flag without a `#tabla_id`
- `AuthUseCase` and controllers are intentionally **not unit-tested** (coupled to `$_SESSION`/`Security`)
- Version tags: no `v` prefix (e.g. `1.12.3`)
- Known, not fixed: sessions are never revalidated against `tbl-usuarios` (a deleted/demoted user keeps the session until PHP GC); deleting a position cascades to its employees without audit rows or file cleanup; `deleteById` returns `execute()`, so deleting a missing ID audits a delete; `database/seeders.sql` creates an admin with a documented password (don't import it in production). The latest audit lives outside the repo (`~/security-audit-skill/Aplicacion_Web_PHP/`)

### Frontend lessons (Bootstrap / DataTables)

- A `<button>` inside an `.input-group` (flex `align-items:stretch`) never shrinks like a sibling `<div>`. Wrap it in a `.input-group-text`-style `<div>` and reset the button (`all:unset; display:flex; align-items:center; justify-content:center; width:100%; height:100%`). For the 44px touch target, do not put `min-height`/`min-width` on the visible box: use an absolute `::before` with `width/height: max(100%, 44px)`.
- Do not patch a shared base selector (`.form-control`, `.btn`) for a local symptom. Compare `getComputedStyle` against a sibling that looks right and scope the fix to the broken element's selector.
- Bootstrap tooltips are lost when DataTables paginates/sorts/searches (rows are replaced). Initialize in a delegated `$(document).on('draw.dt', …)` handler **without** a prior `tooltip('dispose')`, guarding with `!$(this).data('bs.tooltip')`; bump the cache-busting version after core JS changes.
- A hidden HTML template that shares a class with the cloned items breaks any global selector on that class (counts, guards): scope selectors to the real container (`'#container .item'`).
- When assets are versioned with a query string (`?v=filemtime`), sweep every first-party `<script>`/`<link>`, including hand-written ones outside a loop; "the file exists but the browser can't see the helper" is a cached unversioned tag.
- A contrast-tuned color override on a shared selector (especially with `!important`) must ship with its dark-mode counterpart in the same commit, using a _lightened_ value ≥4.5:1 on the real dark backgrounds. Verify both themes with axe-core.
- An a11y audit of the initial state is not enough: open every modal, activate every tab/accordion and audit conditional branches (empty list vs. with data).

### Test lessons

- A fixture/trait that does `DROP`/`CREATE` on a fixed-name resource (DB, file) must suffix the name with a per-process id (`getmypid()`) when tests run in parallel (paratest).
- A test that resets a shared singleton/global (DB connection, config) must restore the test double in `tearDown`, not leave it `null`: with random order, later tests would try to reach the real resource. "Passes locally" proves nothing when local has the real resource; to reproduce CI, run without `.env`/`.env.testing` and with `--order-by=default`.
- Never compute a test's cutoff date with `date()`/`strtotime()` to compare against the DB's `NOW()`/`CURDATE()`: the process clock/timezone and the DB server's can differ. Use an SQL expression (`NOW() - INTERVAL 1 HOUR`).
