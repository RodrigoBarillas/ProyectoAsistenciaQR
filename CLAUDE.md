# ProyectoAsistenciaQR — Backend

API REST en Laravel para un sistema de control de asistencia escolar con QR. Este backend
es consumido por un frontend separado (no vive en este repo).

## Stack y entorno

- Laravel 13, PHP ^8.3 (entorno local corre PHP 8.5 vía `php.new`, instalado en
  `%USERPROFILE%\.config\herd-lite\bin`).
- Base de datos: MySQL (`DB_CONNECTION=mysql`), corriendo en un contenedor Docker local
  (`mysql-DB`, imagen `mysql`, puerto `3306` publicado en el host). Base de datos
  `asistencia_qr`, usuario `root` (sin usuario/base de datos dedicados creados por el
  contenedor — se crearon a mano con `CREATE DATABASE`). Credenciales reales en `.env`
  (no versionado); `.env.example` trae la forma esperada de las variables `DB_*` sin la
  contraseña real. Las pruebas de feature (`php artisan test`) siguen corriendo contra
  SQLite in-memory (`phpunit.xml`), independiente de la base de datos de la app — no
  requiere el contenedor levantado.
- Auth: real, vía JWT (`php-open-source-saver/jwt-auth`, guard `api` en `config/auth.php`
  con driver `jwt`). Implementado por otro miembro del equipo (login/refresh/logout, roles
  y permisos con middleware `permission:<nombre>`) en `routes/api.php` bajo
  `Route::prefix('v1')`. Requiere `JWT_SECRET` en `.env` (generar con
  `php artisan jwt:secret --force` si falta) y en `phpunit.xml` para los tests. `laravel/sanctum`
  sigue instalado pero ya no es el mecanismo de auth activo. Los 3 módulos CRUD
  (Grados/Secciones/Estudiantes) están dentro del grupo `Route::middleware('auth')` y además
  exigen permiso granular por acción vía `->middlewareFor()` en cada `Route::apiResource`:
  `{recurso}.view` (index/show), `{recurso}.create` (store), `{recurso}.edit` (update),
  `{recurso}.delete` (destroy) — p. ej. `grado.view`, `seccion.delete`,
  `estudiante.create`. Estos permisos ya existían en `PermissionEnum`/`PermissionSeeder`
  (hechos por el mismo compañero del módulo de auth) y ya estaban asignados a los roles
  Administrador/Docente/Alumno — solo faltaba conectarlos a las rutas.
- Documentación de API: paquete `laravel/swagger` (en realidad
  `epmyas2022/laravel-swagger` v0.3.0, instalado vía repositorio VCS en `composer.json`,
  **no** `l5-swagger`/`zircote/swagger-php`). Se documenta con **atributos PHP nativos**
  sobre los controladores (`#[SwaggerSection('...')]`, `#[SwaggerSummary('...')]`,
  `#[SwaggerContent(...)]`, `#[SwaggerResponse([...])]`, `#[SwaggerAuth(...)]`,
  `#[SwaggerGlobal([...])]`), no con docblocks `@OA\`. El paquete infiere parámetros de
  ruta y cuerpo de la petición desde los type-hints de los métodos del controlador (p. ej.
  tipar `store(StoreXRequest $request)` documenta el body automáticamente). UI de prueba
  servida en `/docs`; assets estáticos en `public/swagger/`; config en `config/swagger.php`.

## Estructura relevante

```
app/
  Http/
    Controllers/     # un controlador por recurso, con Route::apiResource
    Requests/         # FormRequest por operación de escritura (Store*/Update*)
    Resources/        # JsonResource por recurso (forma de la respuesta)
  Models/             # modelos Eloquent, usan atributos PHP (#[Fillable([...])]) en vez
                       # de las propiedades clásicas $fillable/$hidden
routes/
  api.php             # todas las rutas bajo Route::prefix('v1'); auth/roles/permisos de
                       # otro módulo + Grados/Secciones/Estudiantes, todo dentro de
                       # Route::middleware('auth')
database/
  migrations/         # grados, secciones, estudiantes, asistencias, roles, permissions,
                       # role_permission, administradores, users
docs/
  PLAN-CRUDS.md       # plan detallado de los CRUD de Grados/Secciones/Estudiantes
```

No existen (todavía) carpetas `app/Exceptions` ni `app/Services` propias de estos 3
módulos (sí existen `app/Services/Authentication`, `.../Permission`, `.../Role` del módulo
de auth de otro compañero). La API ya está versionada: todo vive bajo `/api/v1/...`.

## Convenciones de código (establecidas para los módulos CRUD)

- **Modelos**: atributos PHP estilo `App\Models\User` (`#[Fillable([...])]`), casts de
  columnas `estado` a `boolean`, relaciones Eloquent explícitas.
- **Validación**: un `FormRequest` por operación de escritura; `authorize()` devuelve
  `true` por ahora (pendiente de roles/permisos).
- **Respuestas**: un `JsonResource` por recurso; errores vía las respuestas JSON por
  defecto de Laravel (`bootstrap/app.php` ya fuerza `shouldRenderJsonWhen` en rutas `/api/*`).
- **Borrado**: lógico, no físico. Las tablas `grados`, `secciones`, `estudiantes` tienen
  columna `estado` (boolean) y FKs con `restrictOnDelete()`; el método `destroy()` de cada
  controlador pone `estado = false` en vez de eliminar la fila.
- **Swagger**: un `#[SwaggerSection('NombreDelRecurso')]` por controlador, tipar los
  métodos de escritura con su `FormRequest` correspondiente. Ver
  `app/Http/Controllers/ExampleController.php` como plantilla mínima ya existente.

## Modelo de datos — Grados, Secciones, Estudiantes

- `grados`: `id`, `nombre` (string 50, **unique**), `estado` (bool, default true),
  timestamps.
- `secciones`: `id`, `nombre` (string 20), `grado_id` (FK → `grados`, `restrictOnDelete`,
  `cascadeOnUpdate`), `estado` (bool), timestamps. **Unique compuesto** `(grado_id,
  nombre)` — el nombre de sección es único solo dentro de su grado, no globalmente.
- `estudiantes`: `id`, `codigo_estudiante` (string 30, unique), `nombres`/`apellidos`
  (string 100), `qr_token` (uuid, **unique, not nullable** — lo genera el backend con
  `Str::uuid()` al crear, nunca lo envía el cliente ni se edita después), `seccion_id`
  (FK → `secciones`, `restrictOnDelete`, `cascadeOnUpdate`), `estado` (bool), timestamps.
  No hay campos de fecha de nacimiento, DPI ni foto en el esquema actual.

Jerarquía: `Grado hasMany Seccion`, `Seccion belongsTo Grado` + `hasMany Estudiante`,
`Estudiante belongsTo Seccion`.

Otras tablas ya migradas pero fuera del alcance de estos 3 módulos: `asistencias` (FK a
`estudiantes`), `roles`, `administradores` (FK a `users`). Ninguna tiene modelo Eloquent
todavía salvo `User`.

## Git

Cada módulo/feature se trabaja en su propia rama creada desde `main` actualizado
(`feature/crud-grados`, `feature/crud-secciones`, `feature/crud-estudiantes`, ...). Orden
de trabajo de los CRUD: Grados → Secciones → Estudiantes, por la dependencia de llaves
foráneas.

## Pendientes conocidos (fuera de alcance actual)

- Decidir si se expone borrado físico en el futuro (hoy es solo lógico vía `estado`).
- Modelos/controladores para `asistencias`, `administradores` (no son responsabilidad de
  estos 3 módulos). `roles`/`permissions` ya los implementó otro miembro del equipo.
