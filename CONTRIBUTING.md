# Contribuir

Gracias por tu interés en este proyecto. Es un sistema nacido como proyecto académico de Ingeniería de Sistemas que sigue evolucionando — las contribuciones, correcciones y sugerencias son bienvenidas.

## Preparar el entorno

```bash
cp .env.example .env
# Editar .env con credenciales de DB y APP_URL
composer install
mysql -u root -p your_db < database/schema.sql
mysql -u root -p your_db < database/seeders.sql
```

Ver [README.md](README.md#instalación) para requisitos completos y la tabla de variables de entorno.

## Antes de abrir un PR

1. **Corre la suite de tests**: `composer test` — debe pasar en verde. Si agregas lógica en `Domain/Models`, `Http/Requests`, `Services` o `UseCases`, agrega tests unitarios siguiendo la estrategia por capa documentada en `CLAUDE.md`.
2. **Sigue la arquitectura existente**: Controller → Request DTO → UseCase (`OperationResult`) → Service → Repository. Usa el módulo `Employees` como referencia de patrón si vas a agregar algo nuevo.
3. **Respeta las convenciones del proyecto**: CSRF en todo POST, SQL solo con sentencias preparadas (nunca concatenación), nombres de tabla con guión entre backticks, autenticación por `$_SESSION['is_admin']` (nunca por nombre de usuario), puestos/usuarios/auditoría solo para admin (`requireAdmin()`), subidas con nombre aleatorio sin conservar el nombre original, revocar el remember token al cambiar una contraseña, y los errores de conexión a BD se lanzan y se registran (nunca `die()` con el mensaje de PDO).
4. **Si tocas el frontend de login** (`login.php`, `style.css`, `main.js`): no agregues tema oscuro (`prefers-color-scheme: dark`), mantén `#tarjeta` centrado con `margin:auto` (no re-agregues `mb-3`) y conserva la animación continua con guarda `prefers-reduced-motion`. Detalles en `CLAUDE.md` → _Login frontend_.
5. **Si agregas o editas vistas**: sigue las convenciones de accesibilidad ya aplicadas en todo el proyecto — íconos decorativos con `aria-hidden="true"`, botones/links solo-ícono con `aria-label` descriptivo (incluye el nombre de la entidad), inputs de archivo con `aria-describedby` enlazando su `form-text`, `<th>` con `scope="col"`, formularios admin/CRUD con `class="needs-validation ... admin-form|employee-form" novalidate`, y cualquier `<script>`/`<link>` de CDN con `integrity`+`crossorigin`+`defer`. Si la vista tiene una tabla de listado, pasa `'needDataTables' => true` a `renderWithLayout()` en el `index()` del controller. Evita `style="width:..."` inline en `<th>` — usa clases scoped por módulo. Detalles en `CLAUDE.md` → _Frontend_ → _Accessibility conventions_.
6. **Corre `composer dump-autoload`** si agregaste clases nuevas.

## Convención de commits

Se usa el formato [Conventional Commits](https://www.conventionalcommits.org/):

```
feat(módulo): agregar validación de duplicados en puestos
fix(auth): corregir expiración de token remember-me
docs(readme): actualizar tabla de variables de entorno
refactor(events): simplificar registro de listeners
```

Tipos comunes: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`.

## Reportar bugs o proponer features

Abre un issue describiendo:

- Comportamiento esperado vs. actual (para bugs)
- Contexto de uso y justificación (para features)
- Pasos para reproducir, si aplica

## Preguntas de arquitectura

Si no está claro cómo integrar algo nuevo en la estructura de capas, revisa `CLAUDE.md` (contexto de arquitectura) y `PROMPTS.md` (plantillas de trabajo) antes de abrir el PR — documentan las convenciones y patrones esperados del proyecto.
