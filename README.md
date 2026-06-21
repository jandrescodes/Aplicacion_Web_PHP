<div align="center">
  <img src="public/img/deadpool.ico" width="80" alt="Logo">
  <h1>Sistema de Gestión Empresarial PHP</h1>
  <p>Aplicación web construida con arquitectura por capas en PHP, enfocada en la mantenibilidad, escalabilidad y una experiencia de usuario moderna.</p>

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Architecture](https://img.shields.io/badge/architecture-Layered-orange.svg)](#arquitectura)
[![Tests](https://github.com/Jandres25/Aplicacion_Web_PHP/actions/workflows/tests.yml/badge.svg)](https://github.com/Jandres25/Aplicacion_Web_PHP/actions/workflows/tests.yml)

</div>

---

## Descripción

Sistema integral para la gestión de recursos humanos: control de **empleados**, **puestos de trabajo** y **usuarios del sistema**, con generación de cartas de recomendación en PDF y registro de auditoría de acciones.

## Arquitectura

La aplicación usa un framework PHP propio con Composer PSR-4 y separación estricta de responsabilidades por capas:

```
┌─────────────────────────────────────────────────────────────┐
│  HTTP Layer                                                  │
│  routes/web.php              ← Container->resolve(Ctrl)     │
│  app/Http/Controllers/       ← un controller por módulo     │
│  app/Http/Requests/          ← DTOs tipados desde $_POST    │
│  app/Middleware/AuthMiddleware                               │
└──────────────────────────┬──────────────────────────────────┘
                           │ OperationResult / typed Requests
┌──────────────────────────▼──────────────────────────────────┐
│  Application Layer                                           │
│  app/UseCases/               ← orquesta sin awareness HTTP  │
│  app/UseCases/DTOs/          ← OperationResult              │
└──────────────────────────┬──────────────────────────────────┘
                           │ Domain Models / Contracts
┌──────────────────────────▼──────────────────────────────────┐
│  Domain Layer                                                │
│  app/Domain/Models/          ← POPOs (Employee, Position…)  │
│  app/Domain/Contracts/       ← interfaces de repositorio    │
│  app/Services/               ← lógica de negocio            │
└──────────────────────────┬──────────────────────────────────┘
                           │ PDO / archivos
┌──────────────────────────▼──────────────────────────────────┐
│  Infrastructure Layer                                        │
│  app/Repositories/           ← implementan los contratos    │
│  app/Infrastructure/         ← EmployeeFileStorage          │
│  config/Database.php         ← PDO singleton                │
└─────────────────────────────────────────────────────────────┘
  Cross-cutting: core/Container · Router · View · Flash · Security · EventDispatcher
  Config:        config/AppLogger.php (Monolog singleton) · config/events.php (listener registration)
```

**Flujo de una petición POST:**
`index.php` → `Router` → `Controller` → `XxxRequest::fromArray($_POST)` → `validate()` → `UseCase` → `Service` → `Repository` → DB → `OperationResult`

**Flujo de auditoría (cross-cutting via eventos):**
`UseCase` → `EventDispatcher::dispatch(EventoPOPO)` → `AuditListener::handle*()` → `AuditService` → `audit_log`

## Características

- Dashboard de inicio con métricas de conteo por módulo y distribución de empleados por puesto (Bootstrap progress bars, sin librerías de gráficas)
- Arquitectura por capas clásica: HTTP → Application → Domain → Infrastructure
- DI Container liviano con resolución por reflexión (`core/Container.php`)
- Request DTOs tipados — `$_POST` nunca cruza la frontera HTTP
- Domain Models como POPOs con `fromRow()` / `toArray()`
- Interfaces de repositorio para inversión de dependencias
- Variables de entorno cargadas con `vlucas/phpdotenv` (validación de requeridas en bootstrap)
- Logging estructurado con Monolog — rotación diaria en `storage/logs/app.log`, 14 días de retención
- Autenticación con `password_hash` / `password_verify` y sesiones PHP; migración automática de contraseñas en texto plano a bcrypt en el primer login
- Autorización basada en rol (`is_admin` en DB) — `requireAdmin()` lee `$_SESSION['is_admin']`, no el nombre de usuario
- Perfil de usuario: cambio de nombre de usuario, correo y contraseña con verificación de contraseña actual; actualiza `$_SESSION['usuario']` si cambia el nombre
- "Recuérdame" con token rotante almacenado hasheado en DB y cookie `HttpOnly`/`SameSite=Lax`
- Protección CSRF en formularios y peticiones AJAX (meta tag + header)
- Eliminación asíncrona con AJAX + SweetAlert2 sin recargar la página
- Notificaciones Flash integradas con SweetAlert2 con mensajes específicos por módulo y acción
- DataTables con búsqueda, paginación, diseño responsivo y exportación de reportes (PDF, Excel, CSV, impresión) con botón de visibilidad de columnas
- Generación de cartas de recomendación en PDF con dompdf (abre inline en el visor del navegador)
- Prevención de SQL injection con sentencias preparadas PDO
- Auditoría de acciones: registro append-only de create/update/delete por entidad y usuario (`audit_log`); vista admin en `GET /auditoria` con DataTables y exportación
- EventDispatcher propio (sin librerías): desacopla UseCases de `AuditService` mediante eventos de dominio (POPOs en `app/Domain/Events/`) y un `AuditListener`; los UseCases emiten eventos, el listener los traduce a llamadas de auditoría

## Testing

Suite de tests unitarios con **PHPUnit 10.5** — sin base de datos, sin I/O real.

```bash
# Correr todos los tests
composer test

# Solo la suite Unit
composer test:unit
```

| Capa            | Estrategia                     | Archivos                    |
| --------------- | ------------------------------ | --------------------------- |
| Domain Models   | Unit puro                      | `tests/Unit/Domain/Models/` |
| Request DTOs    | Unit puro                      | `tests/Unit/Http/Requests/` |
| OperationResult | Unit puro                      | `tests/Unit/UseCases/DTOs/` |
| Services        | Unit con mocks de repositorios | `tests/Unit/Services/`      |
| UseCases        | Unit con mock del Service      | `tests/Unit/UseCases/`      |

El workflow de GitHub Actions corre la suite en PHP 8.2 y 8.3 en cada push y pull request a `master`.

## Dependencias

| Paquete              | Versión | Uso                                        |
| -------------------- | ------- | ------------------------------------------ |
| `dompdf/dompdf`      | ^3.1    | Generación de PDF (carta de recomendación) |
| `vlucas/phpdotenv`   | ^5.6    | Carga y validación de variables de entorno |
| `monolog/monolog`    | ^3.10   | Logging estructurado con rotación de logs  |
| `symfony/var-dumper` | ^7.4    | Debug (`dump()` / `dd()`) — solo dev       |

Todas las dependencias se instalan con `composer install`.

## Instalación

**Requisitos:** PHP 8.0+, Apache/Nginx, MySQL/MariaDB, Composer.

```bash
# 1. Copiar y configurar el entorno
cp .env.example .env
# Editar .env con credenciales DB y APP_URL

# 2. Instalar dependencias
composer install

# 3. Importar base de datos
mysql -u root -p your_db < database/schema.sql
mysql -u root -p your_db < database/seeders.sql
```

Apuntar el servidor web a `public/` o acceder vía `http://localhost/Aplicacion_Web_PHP/public/`.

## Logs

Los logs de la aplicación se escriben en `storage/logs/app.log` con rotación diaria (14 archivos).

- En `APP_ENV=local` se registran desde nivel `Debug`; en producción solo `Warning` en adelante.
- Los errores fatales y excepciones no atrapadas se registran automáticamente desde el bootstrap.

## Frontend

- Bootstrap 5 + FontAwesome 6 (CDN)
- jQuery + DataTables 1.13 + Buttons 2.3 (exportación PDF/Excel/CSV/Print + visibilidad de columnas)
- SweetAlert2

## Base de datos

Tablas: `tbl-empleados`, `tbl-puestos`, `tbl-usuarios` (los guiones requieren comillas en SQL), `audit_log`.  
`tbl-usuarios` incluye `remember_token` y `remember_token_expires` para "Recuérdame", e `is_admin TINYINT(1)` para control de acceso por rol.  
`audit_log` es append-only (`id`, `user_id` NULL, `action` ENUM, `entity` ENUM, `entity_id` NULL, `created_at`); `user_id` sin FK para sobrevivir borrados de usuario.  
La columna `Correo` tiene restricción `UNIQUE` — la unicidad se valida explícitamente en `UserService` antes del INSERT/UPDATE para dar mensajes de error claros.  
Archivos subidos en `public/storage/uploads/`. Los assets por defecto (`user-default.jpg`, `cv_default.pdf`) están protegidos contra borrado accidental.

---

<div align="center">
  <p>&copy; 2026 Desarrollado por <b>Jose Andres Meneces Lopez</b> - Proyecto de Ingeniería de Sistemas</p>
</div>
