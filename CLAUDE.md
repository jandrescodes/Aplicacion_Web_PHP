# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Running the Application

- **Stack**: XAMPP (Apache + MySQL). The app runs at `http://localhost/Aplicacion_Web_PHP/public/`.
- **Start XAMPP**: `sudo /opt/lampp/lampp start`
- **Apache logs**: `/opt/lampp/logs/error_log`
- **PHP errors**: Check Apache error log or enable `display_errors` in `/opt/lampp/etc/php.ini`
- **App logs**: `storage/logs/app.log` (Monolog, daily rotation)

## Setup

```bash
cp .env.example .env
# Edit .env with DB credentials and APP_URL
composer install
```

Import the schema and seed data:

```bash
mysql -u root -p your_db < database/schema.sql
mysql -u root -p your_db < database/seeders.sql
```

Autoloading is handled by Composer PSR-4. Run `composer dump-autoload` after adding new classes.

## Architecture

This is a custom PHP MVC framework using Composer PSR-4 autoloading (no manual `require_once`).

**Request lifecycle:**

1. `public/index.php` — front controller; loads `vendor/autoload.php`, initializes `vlucas/phpdotenv` (validates required env vars), builds `Config\AppLogger` (Monolog), builds `Core\Container`, registers bindings from `config/container.php`, dispatches
2. `routes/web.php` — resolves all 5 HTTP controllers via `$container->resolve(XController::class)` and maps all routes
3. `core/Router.php` — matches URI to handler; also serves static files from `public/`

**Layer responsibilities:**

| Layer          | Path                                | Role                                                                                                |
| -------------- | ----------------------------------- | --------------------------------------------------------------------------------------------------- |
| HTTP           | `app/Http/Controllers/`             | One controller per module; validates CSRF, builds Request DTOs, calls UseCases, renders views       |
| HTTP           | `app/Http/Requests/`                | Typed DTOs built from `$_POST`; `fromArray()` + `validate()` — `$_POST` never crosses this boundary |
| Application    | `app/UseCases/`                     | Orchestrates domain logic; receives typed Requests, returns `OperationResult`; no HTTP awareness    |
| Application    | `app/UseCases/DTOs/`                | `OperationResult` — typed result replacing `['success' => bool, 'message' => string]` arrays        |
| Domain         | `app/Domain/Models/`                | POPOs (`Employee`, `Position`, `User`); `fromRow(array): self` + `toArray(): array`                 |
| Domain         | `app/Domain/Contracts/`             | Repository interfaces (`EmployeeRepositoryInterface`, etc.)                                         |
| Domain         | `app/Services/`                     | Business logic; depends on repository interfaces, not concrete classes                              |
| Infrastructure | `app/Repositories/`                 | PDO queries; implement the repository contracts                                                     |
| Infrastructure | `app/Infrastructure/`               | `EmployeeFileStorage` — file upload and deletion                                                    |
| Infrastructure | `config/Database.php`               | PDO singleton (`Config\Database::getConnection()`); reads from `$_ENV`                              |
| Infrastructure | `config/AppLogger.php`              | Monolog singleton (`Config\AppLogger::getInstance()`); logs to `storage/logs/app.log`               |
| Cross-cutting  | `core/Container.php`                | Lightweight DI container; resolves dependencies via `ReflectionClass`; used only in bootstrap       |
| Cross-cutting  | `core/`                             | `Router`, `View`, `Flash`, `Security`, `ErrorPage`                                                  |
| Cross-cutting  | `app/Middleware/AuthMiddleware.php` | Checks `$_SESSION['logueado']`, falls back to `AuthUseCase::handleRememberLogin()`                  |

**Environment variables:**
`vlucas/phpdotenv` loads `.env` in `public/index.php` before anything else. It validates that `APP_URL`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` are present, and checks types for `REMEMBER_ME_ENABLED` (boolean) and `REMEMBER_ME_LIFETIME` (integer). All code reads env vars directly from `$_ENV['KEY']`.

**DI Container:**
`config/container.php` registers bindings: `PDO` as singleton via closure, and the three repository interface → concrete class mappings. The Container is only used in bootstrap (`public/index.php` and `routes/web.php`) — never injected into domain or application classes.

**Logging:**
`Config\AppLogger::getInstance()` returns a Monolog `Logger`. Logs rotate daily in `storage/logs/` and are kept for 14 days. In `APP_ENV=local` all levels from `Debug` up are logged; in production only `Warning` and above. Fatal errors and unhandled exceptions are automatically logged from the bootstrap shutdown handler and catch block.

**Request DTOs pattern:**

```
Controller POST method:
  1. hasValidCsrfToken($_POST)        — CSRF check
  2. XxxRequest::fromArray($_POST)    — build typed DTO
  3. $req->validate()                 — validate; Flash + redirect on errors
  4. $useCase->action($req)           — pass DTO to UseCase
  5. check OperationResult->success   — render or redirect
```

**Rendering:**
`Controller::renderWithLayout()` delegates to `Core\View::renderWithLayout()`, which wraps views with `resources/views/layout/{header,module_header,footer}.php`. Page metadata (title, breadcrumbs, icon) is built via `pageHeaderData()` and `moduleBreadcrumbs()` on the base `Controller`.

**Intelephense false positives:** Variables like `$public_base`, `$nombreUsuario`, `$flash`, `$csrfToken` in layout views, and `$nombreCompleto`, `$puesto`, `$fechaIngreso`, `$diferencia`, `$fechaActual` in `recommendation_letter.php`, will show as "undefined" in the IDE because they come from `extract($data)` inside `View::render()`. These are not real errors.

## Remember Me (cookie persistence)

- Enabled by default; controlled by `REMEMBER_ME_ENABLED=true` in `.env`
- Cookie lifetime: `REMEMBER_ME_LIFETIME=30` (days)
- **Cookie format**: `{userId}:{plainToken}` — the plain token is never stored; only its `sha256` hash goes into the DB column `remember_token` on `tbl-usuarios`
- **Rotation**: every successful cookie-based login issues a new token and overwrites the old one (token rotation)
- **Logout**: `AuthUseCase::handleLogout()` revokes the DB token and expires the cookie; `AuthController` calls this via `$this->authUseCase->handleLogout()`
- `Security::setRememberCookie()` / `clearRememberCookie()` / `getRememberCookie()` in `core/Security.php` manage the raw cookie with `HttpOnly`, `SameSite=Lax`, and `Secure` (auto-detected)
- Schema columns added to `tbl-usuarios`: `remember_token VARCHAR(64) NULL`, `remember_token_expires DATETIME NULL`

## CSRF Protection

- **Forms**: include a hidden `csrf_token` field; use `Security::getCsrfToken()` to generate it
- **AJAX**: read the token from the `<meta name="csrf-token">` tag in the layout and send it as `X-CSRF-Token` header; validate server-side with `Security::isValidCsrfToken()`
- All mutating routes validate CSRF before processing

## PDF Generation

- **Library**: `dompdf/dompdf` ^3.1 (installed via Composer — no `libs/` directory).
- **Usage**: `View::capture($view, $data)` renders a view to a string; pass it to `$dompdf->loadHtml($html, 'UTF-8')`.
- **External CSS**: dompdf cannot resolve `<link>` tags reliably. Embed styles with `<style><?= file_get_contents(__DIR__ . '/path/to/file.css'); ?></style>` — the CSS file lives in `public/css/` and is injected at render time.
- **Inline vs download**: `$dompdf->stream($filename, ['Attachment' => false])` opens the PDF in the browser viewer; `true` forces download.
- **Current use**: `EmployeesController::recommendation()` generates a one-page formal recommendation letter (`resources/views/employees/recommendation_letter.php`) styled by `public/css/recommendation_letter.css`.

## Testing

- **Framework**: `phpunit/phpunit` ^10.5 (devDependency vía Composer).
- **Correr tests**: `composer test` (todos) · `composer test:unit` (solo Unit).
- **Estructura**: `tests/Unit/` con subdirectorios por capa — `Domain/Models/`, `Http/Requests/`, `Services/`, `UseCases/`, `UseCases/DTOs/`.
- **Estrategia por capa**:
  - Domain Models y Request DTOs → unit puro, sin mocks.
  - Services → unit con `$this->createMock(XxxRepositoryInterface::class)` y `createMock(EmployeeFileStorage::class)`.
  - UseCases → unit con mock del Service correspondiente.
  - Controllers y `AuthUseCase` → **no se testean** (acoplamiento a `$_SESSION`, `Security`, HTTP).
- **`AuthUseCase` excluido**: depende de `Security::startSession()`, `session_regenerate_id()` y `$_SESSION` directamente — no se puede aislar sin refactor.
- **Namespace de tests**: `Tests\` mapeado a `tests/` en `autoload-dev` del `composer.json`.
- **CI**: `.github/workflows/tests.yml` corre la suite en PHP 8.2 y 8.3 en cada push/PR a `master`.

## Frontend

- Bootstrap 5 + FontAwesome 6 (CDN)
- DataTables 1.13 + Buttons 2.3 for listings — includes PDF, Excel, CSV, Print, and column visibility buttons
- SweetAlert2 for confirmations and toast notifications
- AJAX deletion pattern: JS in `public/js/{employees,positions,users}.js` calls POST endpoints and removes the table row on success without reloading

**DataTables Buttons setup:**
- CDN scripts (JSZip, pdfmake, Buttons core/bootstrap5/html5/print/colvis) are loaded in `layout/footer.php` with SRI hashes.
- All three listing tables (`#tabla_id`) carry a `data-module` attribute (`users`, `employees`, `positions`).
- A single DataTable init in `public/js/main.js` reads `data-module` to apply per-module config: report titles, filenames, export column indices, and localized "sInfo" strings.
- Export columns per module: users `[0,1,2]`, employees `[0,1,4,5]` (name, position, date — skips photo/CV/actions), positions `[0,1]`.
- Module scripts (`employees.js`, etc.) only contain AJAX delete logic and load before DataTables; the init must stay in `main.js`.

## Database

Tables: `tbl-puestos`, `tbl-empleados`, `tbl-usuarios` (note hyphen in table names — always quote them in SQL). `tbl-usuarios` has extra columns: `remember_token`, `remember_token_expires` (remember-me), and `is_admin TINYINT(1) NOT NULL DEFAULT 0` (role-based authorization).

**Admin authorization:** `Controller::requireAdmin()` checks `$_SESSION['is_admin']`, which is set from `$user->isAdmin` on login (`AuthUseCase`). Do not use username string comparison for access control. `Controller::renderWithLayout()` always passes `isAdmin` (bool) to every view via the layout data — the navbar uses `!empty($isAdmin)` to show the Users menu, never the username string.

**No migration runner:** schema changes require two steps — update `database/schema.sql` AND apply directly:

```bash
mysql -u root -proot -h 127.0.0.1 app -e "ALTER TABLE ..."
```

File uploads land in `public/storage/uploads/`. Default assets (`user-default.jpg`, `cv_default.pdf`) live there too. Never pass these to `deleteFileIfExists()` — `EmployeeService` guards against this with an explicit `$defaultFiles` check.

## User profile

`GET /perfil` and `POST /perfil-datos` / `POST /perfil-contrasena` are handled by `ProfileController` → `ProfileUseCase` → `UserService`.

- **Data update** (`perfil-datos`): changes username and email. `UserService::updateProfile()` checks uniqueness via `usernameExistsExcluding()` and `emailExists()` (both exclude the current user's ID). If the username changes, `ProfileUseCase::updateData()` refreshes `$_SESSION['usuario']` — the only place outside Auth allowed to write session state.
- **Password change** (`perfil-contrasena`): separate form, separate route. `ProfileUseCase::changePassword()` calls `UserService::verifyCurrentPassword()` first, then `UserService::changePassword()` which calls `UserRepository::updatePasswordHash()`. The data-update path never touches the password column.
- `ProfileController` reads `$_SESSION['user_id']` (never from `$_POST`) so a user can only edit their own profile.
- **Two forms, two routes** — never mix them. The earlier single-form + hidden-fields approach caused duplicate POST keys where PHP used the last value, silently discarding typed input.

## Password hashing

`AuthService::authenticate()` auto-migrates plain-text passwords to bcrypt on the first successful login — do not add migration logic elsewhere. `UserService` always calls `password_hash()` on create and update. The seeder (`database/seeders.sql`) stores bcrypt hashes; comments above the INSERT lines show the plain-text values for local dev reference.

## Logs

App logs are written to `storage/logs/app.log`. The directory is tracked in git via `.gitkeep`; log files are gitignored. Do not delete the `storage/logs/` directory.
