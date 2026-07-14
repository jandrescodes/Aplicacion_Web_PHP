# PROMPTS.md — Sistema de Gestión de Empleados

> Plantillas de prompts para el proyecto. Úsalas como base — adapta los bloques
> `[Tarea]` y `[Contexto]` a lo que necesites en cada sesión.
> **Requisito:** Carga [CLAUDE.md](CLAUDE.md) al inicio de cada sesión (arquitectura, convenciones, stack).

---

## Cómo usar este archivo

Cada plantilla sigue la estructura de 5 ejes del prompt profesional:

| Eje                   | Pregunta          | Para qué sirve                                    |
| --------------------- | ----------------- | ------------------------------------------------- |
| **Rol**               | ¿Quién eres?      | Define el nivel y especialidad que asume la IA    |
| **Contexto**          | ¿Dónde estamos?   | El proyecto, stack y módulo activo                |
| **Tarea exacta**      | ¿Qué necesitas?   | Concreto y específico — nunca genérico            |
| **Restricciones**     | ¿Qué límites hay? | Convenciones del proyecto que NO se pueden romper |
| **Formato de salida** | ¿Cómo lo quieres? | Estructura del output esperado                    |

> **Regla de oro:** Cuanto más específico sea el bloque `[Tarea]`,
> menos correcciones necesitarás después.

**Reglas de uso:**

- **Carga el CLAUDE.md primero** — contiene arquitectura de capas, ciclo de vida de la request, convenciones y stack.
- **Un prompt por subtarea.** Pedir "el módulo completo" en un solo prompt produce resultados genéricos.
- **Si el output no encaja**, no corrijas manualmente primero — ajusta `[Restricciones]` y repite.
- **El spec antes que el código.** Define qué debe hacer antes de pedir que lo implemente.
- **Guarda los prompts que funcionen bien** en este archivo como nuevas plantillas.

---

## Plantilla base (copia esto y rellena)

> **Antes de usar:** Asegúrate de tener [CLAUDE.md](CLAUDE.md) cargado.

```
[Rol]
Actúa como desarrollador PHP Senior especializado en arquitectura por capas
y patrones de diseño (Repository, UseCase, Service, DI Container).

[Contexto]
(Arquitectura, stack y convenciones ya están en CLAUDE.md — cárgalo antes de usar esta plantilla)
Módulo activo: _______________

[Tarea]
_______________

[Restricciones]
- Usar el módulo Employees como referencia exacta de patrón
  (EmployeesController / EmployeeUseCase / EmployeeService / EmployeeRepository)
- Renderizado: $this->renderWithLayout('vista.php', array_merge($data, $this->pageHeaderData(...)))
- CSRF obligatorio en todo POST: $this->hasValidCsrfToken($_POST)
- Acceso protegido: $this->requireLogin() al inicio de cada método
- Request DTOs: XxxRequest::fromArray($_POST) + ->validate() antes de llamar al UseCase
- Los UseCases reciben Request DTOs tipados y devuelven OperationResult
- Flash messages: Flash::set('mensaje') para éxito, Flash::set('mensaje', 'error') para error
- Redirecciones: $this->redirect('nombre-ruta')
- AJAX delete: detectar $this->isAjaxRequest() → header JSON + json_encode(['success' => bool, 'message' => '...']) + exit
- Los datos llegan a las vistas vía extract($data) — nombrar variables en snake_case
- SQL: siempre bindParam() / bindValue() con PDO — nunca concatenar variables
- Nombres de tablas con guión siempre entre backticks: `tbl-empleados`
- Los Controllers se resuelven automáticamente vía DI Container — no instanciar con new

[Formato de salida]
_______________
```

---

## Plantilla 1 — Generar código nuevo (feature)

Usar cuando: implementar un módulo o funcionalidad nueva.

```
[Rol]
Actúa como desarrollador PHP Senior especializado en arquitectura por capas
y patrones de diseño (Repository, UseCase, Service, DI Container).

[Contexto]
(Arquitectura, stack y convenciones ya están en CLAUDE.md — cárgalo antes de usar esta plantilla)
Módulo activo: [nombre del módulo — ej: empleados, puestos, usuarios]

Archivos relevantes del módulo:
- app/Http/Controllers/[Modulo]Controller.php
- app/Http/Requests/[Modulo]/Store[Modulo]Request.php
- app/Http/Requests/[Modulo]/Update[Modulo]Request.php
- app/UseCases/[Modulo]UseCase.php
- app/Services/[Modulo]Service.php
- app/Repositories/[Modulo]Repository.php
- app/Domain/Models/[Modulo].php
- app/Domain/Contracts/[Modulo]RepositoryInterface.php
- resources/views/[modulo]/[vista].php
- public/js/[modulo].js (si tiene AJAX)

[Tarea]
Implementar [nombre exacto del requerimiento].

Descripción: [describe qué debe hacer]

[Restricciones]
- Usar EmployeesController / EmployeeUseCase / EmployeeService / EmployeeRepository como referencia exacta
- Renderizado: $this->renderWithLayout() con array_merge() + $this->pageHeaderData() + $this->moduleBreadcrumbs()
- CSRF en todo POST: if (!$this->hasValidCsrfToken($_POST)) { Flash::set(..., 'error'); $this->redirect(...); }
- $this->requireLogin() al inicio de cada método del controller
- Request DTO: Store[Modulo]Request::fromArray($_POST) + ->validate() antes de llamar al UseCase
- UseCase recibe el DTO tipado y devuelve OperationResult
- Validaciones de campos en el Request DTO (método validate()); validaciones de negocio en el Service
- El Repository implementa la interfaz de su contrato en app/Domain/Contracts/
- Registrar el binding interfaz → clase concreta en config/container.php
- Eliminación: detectar $this->isAjaxRequest() → JSON response; sino → Flash + redirect
- AJAX delete en frontend: fetch con FormData (incluye csrf_token) + SweetAlert confirm + toast de resultado
- SQL: bindParam() / bindValue() con tipos explícitos (PDO::PARAM_INT, PDO::PARAM_STR)
- Tablas con guión entre backticks en todas las queries
- Variables de vista en snake_case; las vistas las reciben vía extract($data)
- Domain Model con fromRow(array $row): self y toArray(): array
- UseCase llama ->toArray() al pasar datos a la vista para compatibilidad con extract()

[Formato de salida]
Devuelve en este orden:
1. Lista de archivos que se crean o modifican
2. app/Domain/Models/[Modulo].php
3. app/Domain/Contracts/[Modulo]RepositoryInterface.php
4. app/Repositories/[Modulo]Repository.php (implements la interfaz)
5. app/Services/[Modulo]Service.php
6. app/UseCases/[Modulo]UseCase.php
7. app/Http/Requests/[Modulo]/Store[Modulo]Request.php
8. app/Http/Requests/[Modulo]/Update[Modulo]Request.php
9. app/Http/Controllers/[Modulo]Controller.php
10. resources/views/[modulo]/index.php, create.php, edit.php
11. public/js/[modulo].js (AJAX delete)
12. Líneas a agregar en routes/web.php
13. Binding a agregar en config/container.php
14. Queries SQL si hay cambios en BD (CREATE TABLE, ALTER)
15. Checklist de testing manual (flujo exitoso + casos de error + AJAX delete)
```

---

## Plantilla 2 — Debuggear un error

Usar cuando: algo no funciona y no está claro por qué.

```
[Rol]
Actúa como desarrollador PHP Senior especializado en debugging
de aplicaciones PHP custom con PDO y arquitectura por capas.

[Contexto]
Proyecto: Sistema de Gestión de Empleados — PHP 8.x, MySQL, PDO.
URL: http://localhost/Aplicacion_Web_PHP/public/
Logs Apache: /opt/lampp/logs/error_log
Logs de la app: storage/logs/app.log (Monolog, rotación diaria)
Archivo donde ocurre el error: [ruta completa]
Método/función afectada: [nombre]

[Tarea]
Tengo este error:
[pega el mensaje de error exacto o el comportamiento inesperado]

Código actual:
[pega el bloque de código relevante — no todo el archivo]

Lo que debería hacer:
[describe el comportamiento esperado]

Lo que intenté que no funciona:
[describe lo que ya probaste]

[Restricciones]
- No cambiar la arquitectura del archivo — solo corregir el problema específico
- Mantener las convenciones de naming y capas del proyecto
- Si el fix requiere cambiar más de un archivo, indicarlo antes de proponer código
- No agregar require_once ni includes — el autoloading es PSR-4 vía Composer

[Formato de salida]
1. Diagnóstico: causa raíz del error en 2-3 líneas
2. Fix: código corregido con comentario solo si el cambio no es obvio
3. Por qué pasó: explicación breve para no repetirlo
```

---

## Plantilla 3 — Code review antes del merge

Usar cuando: antes de hacer merge de una rama, o cuando el código funciona
pero algo "huele mal".

```
[Rol]
Actúa como Tech Lead PHP con experiencia en code review de sistemas por capas,
seguridad web y patrones de diseño.

[Contexto]
(Arquitectura ya está en CLAUDE.md — cárgalo antes de usar esta plantilla)
Rama revisada: feature/[nombre]
Requerimiento implementado: [nombre del requerimiento]

[Tarea]
Revisa el siguiente código antes del merge.

[pega el código o el diff]

[Restricciones]
Evalúa específicamente:
- Seguridad: SQL injection (bindParam/bindValue), XSS (htmlspecialchars en vistas), CSRF en POST, requireLogin() presente, requireAdmin() usa $_SESSION['is_admin'] (no comparación de nombre de usuario); navbar usa !empty($isAdmin) no $nombreUsuario == "Administrador"
- Capas: SQL fuera del Repository, lógica de negocio fuera del Service, lógica HTTP fuera del Controller
- Request DTOs: $_POST solo en Controllers; XxxRequest::fromArray($_POST) + ->validate() antes del UseCase
- OperationResult: UseCases devuelven OperationResult, no arrays crudos
- Domain Models: Repositories devuelven objetos tipados, no arrays; UseCase llama ->toArray() para vistas
- Repository Contracts: Services dependen de la interfaz, no de la clase concreta
- DI Container: nuevos bindings registrados en config/container.php si hay nueva interfaz
- Renderizado: usa renderWithLayout() con pageHeaderData() y moduleBreadcrumbs() correctamente
- AJAX delete: isAjaxRequest() verificado, JSON con {success, message}, exit al final
- Flash: Flash::set('msg') para éxito, Flash::set('msg', 'error') para error — nunca otras claves
- Archivos: manejo defensivo de subidas (extensión, rollback si falla el INSERT)
- Tablas: nombres con guión siempre entre backticks en SQL
- Variables de vista: en snake_case, pasadas con compact() o array_merge()

[Formato de salida]
OK  - Lo que está bien (al menos 2 puntos)
OBS - Observaciones (mejoras no críticas, con sugerencia)
FIX - Problemas a corregir antes del merge (con código corregido)
```

---

## Plantilla 4 — Consulta de arquitectura

Usar cuando: hay una decisión técnica importante antes de implementar,
o cuando no está claro cómo integrar algo nuevo.

```
[Rol]
Actúa como arquitecto de software PHP con experiencia en arquitectura por capas
y PHP sin framework.

[Contexto]
(Arquitectura, capas y stack ya están en CLAUDE.md — cárgalo antes de usar esta plantilla)
Estado actual: módulos implementados — dashboard, empleados, puestos, usuarios, auth, perfil, auditoría.

[Tarea]
Necesito decidir: [describe la decisión técnica]

Opciones que estoy considerando:
- Opción A: [describe]
- Opción B: [describe]

[Restricciones]
- No introducir frameworks (ni Laravel, ni Symfony)
- Mantener compatibilidad con Router, View y Autoloader actuales (core/)
- No introducir librerías JS nuevas sin justificación fuerte
- La solución debe integrarse en la estructura de capas existente
- Considerar impacto en config/container.php si se agrega una nueva dependencia

[Formato de salida]
1. Recomendación directa (cuál opción y por qué en 3 líneas)
2. Trade-offs de cada opción (tabla si aplica)
3. Impacto en el resto del sistema
4. Primeros pasos concretos para implementar la opción recomendada
```

---

## Plantilla 5 — Escribir tests PHPUnit

Usar cuando: agregar tests a una capa existente o a un módulo nuevo.

```
[Rol]
Actúa como desarrollador PHP Senior especializado en testing con PHPUnit 10.x,
mocks de interfaces y arquitectura por capas.

[Contexto]
(Arquitectura ya está en CLAUDE.md — cárgalo antes de usar esta plantilla)
Testing: PHPUnit 10.5 — suite Unit en tests/Unit/, namespace Tests\ mapeado por PSR-4.
Estrategia por capa:
- Domain Models (app/Domain/Models/): unit puro — fromRow() / toArray(), sin mocks.
- Request DTOs (app/Http/Requests/): unit puro — fromArray() + validate(), sin mocks.
- Services (app/Services/): unit con createMock() de la interfaz de repositorio y de EmployeeFileStorage.
- UseCases (app/UseCases/): unit con createMock() del Service correspondiente.
- Controllers y AuthUseCase: NO se testean (acoplados a $_SESSION y Security).

Módulo / capa a testear: [nombre]
Archivo fuente: [ruta]

[Tarea]
Escribe los tests PHPUnit para [clase/método específico].

[Restricciones]
- Namespace: Tests\Unit\[subcarpeta según capa]
- Usar $this->createMock(Interface::class) — nunca instanciar repositorios reales
- setUp() inicializa mocks y el SUT (System Under Test)
- Nombrar métodos: test_[método]_[escenario]_[resultado esperado]()
- Un assert principal por test; asserts adicionales solo si son parte del mismo contrato
- Usar $this->callback(fn($arg) => ...) para verificar contenido de arrays pasados al mock
- No mockear lo que no se puede aislar (Security, $_SESSION, funciones globales de PHP)
- No usar @covers ni anotaciones — PHPUnit 10 las deprecó
- Correr vendor/bin/phpunit --testsuite Unit para verificar antes de entregar

[Formato de salida]
1. Ruta del archivo de test
2. Código completo del test class
3. Resultado esperado de vendor/bin/phpunit (tests, assertions, tiempo)
```

_Última actualización: 2026-06-21 — EventDispatcher implementado: UseCases emiten eventos de dominio (POPOs en `app/Domain/Events/`); `AuditListener` los traduce a `AuditService`; registro en `config/events.php`; `EventDispatcher` singleton en `core/`. `AuditService` ya no se inyecta directamente en UseCases de negocio._
_Mantener sincronizado con CLAUDE.md al inicio de cada sesión._
