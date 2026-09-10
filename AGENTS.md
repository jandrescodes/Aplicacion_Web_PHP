# AGENTS.md

Custom PHP 8 MVC framework (no Laravel). Runs only under XAMPP:
Apache + MySQL at `http://localhost/Aplicacion_Web_PHP/public/`.

> Full architecture, conventions, and domain rules are in `CLAUDE.md` — read it first when making changes.

## Commands (run in project root)

- Start/stop server: `sudo /opt/lampp/lampp start` / `... stop`
- Tests: `composer test:unit` (only suite that exists — CI runs `vendor/bin/phpunit --testsuite Unit`)
- Regenerate autoload after adding classes: `composer dump-autoload`
- Import schema/seed: `mysql -u root -proot -h 127.0.0.1 your_db < database/schema.sql` (then `seeders.sql`)
- Schema changes: edit `database/schema.sql` **AND** apply via `ALTER TABLE` directly (no migration runner)
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
- **Remember me**: cookie `{userId}:{plainToken}`, plain token never stored (sha256 in DB); rotation + sliding expiry are intentional—do not "fix"
- **Login animation** in `public/js/main.js` loops continuously (`complete: randomValues`); guard with `prefers-reduced-motion`, don't convert to single-pass
- **Accessibility conventions apply repo-wide**: decorative icons get `aria-hidden="true"`, icon-only buttons/links get `aria-label` including the entity name (e.g. `aria-label="Editar a {name}"`), file inputs link helper text via `aria-describedby="{field}-help"`, `<th>` gets `scope="col"`, CDN `<script>`/`<link>` tags need `integrity`+`crossorigin`+`defer`, admin/CRUD forms use `needs-validation` + `novalidate` (submit handler lives once in `main.js`). See `CLAUDE.md` → *Frontend* → *Accessibility conventions*. Don't set `style="visibility: hidden;"` inline on `#tabla_id` — `main.js` does it via JS right before DataTables init
- **Views**: `variables` like `$nombreUsuario`, `$flash` come from `extract($data)` in `Core\View::render()` — those "undefined" IDE warnings are false positives
- PDFs: dompdf only; embed CSS via `<style><?= file_get_contents('public/css/x.css'); ?></style>` (link tags unreliable)
- Files upload to `public/storage/uploads/`; never `deleteFileIfExists()` on `user-default.jpg` / `cv_default.pdf` (guarded in `EmployeeService`)

## Undocumented details worth knowing

- DataTables init lives in `public/js/main.js` (reads `data-module` on `#tabla`); module JS files only hold AJAX delete — don't move the init
- DataTables assets are **conditionally loaded**: controllers pass `['needDataTables' => true]` to `renderWithLayout()` in `index()` (Employees, Positions, Users, Audit); default is `false` in `Core\View`. New listing views must opt in, and don't set the flag without a `#tabla_id`
- `AuthUseCase` and controllers are intentionally **not unit-tested** (coupled to `$_SESSION`/`Security`)
- Version tags: no `v` prefix (e.g. `1.12.3`)