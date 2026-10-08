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
- CORS: `config/cors.php` existe (antes no estaba publicado, así que `HandleCors` —activo por
  defecto en Laravel— no agregaba ningún header porque `cors.paths` quedaba vacío: CORS
  estaba efectivamente apagado). Solo aplica a `api/*`. Orígenes permitidos vía
  `CORS_ALLOWED_ORIGINS` en `.env` (coma-separados); el default cubre puertos típicos de
  Vite/CRA en local hasta que el frontend tenga una URL definitiva.
  `supports_credentials` queda en `false` porque la auth es JWT por header `Authorization`,
  no por cookies.
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
  migrations/         # grados, secciones, estudiantes, horarios, asistencias, roles,
                       # permissions, role_permission, administradores, users
  factories/          # Grado/Seccion/Estudiante/Horario/Asistencia/User
  seeders/            # Horario/Role/Permission/User + Grado/Seccion/Estudiante/Asistencia
                       # (datos de ejemplo encadenados para poder probar el flujo completo
                       # sin Tinker)
docs/
  PLAN-CRUDS.md       # plan detallado de los CRUD de Grados/Secciones/Estudiantes (no
                       # cubre Asistencia/Horario, documentados más abajo en este archivo)
```

No existen (todavía) carpetas `app/Exceptions` propias de estos módulos (sí existen
`app/Services/Authentication`, `.../Permission`, `.../Role` del módulo de auth de otro
compañero, y `app/Http/Mock/` con clases `abstract` de solo constantes usadas como ejemplos
de respuesta para los atributos `#[SwaggerResponse(...)]`, p. ej. `AsistenciaMock`). La API
ya está versionada: todo vive bajo `/api/v1/...`. No existe modelo/controlador para
`administradores` (solo la migración).

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
- **Tests**: un `tests/Feature/{Recurso}ControllerTest.php` por controlador, usando el
  trait `tests/Concerns/AuthenticatesWithPermissions.php` (`actingAsUserWithPermissions()`)
  para autenticar con un rol de prueba que solo tiene los permisos listados. Excepción: los
  tests de `AsistenciaController` no pueden usar ese helper tal cual para los casos de
  alumno, porque `registrar()`/`historial()` resuelven el `Estudiante` autenticado
  comprobando el **nombre del rol** (`RoleEnum::ALUMNO`) y `estudiantes.user_id`, no solo el
  permiso — ver el helper local `actingAsAlumno()` en
  `tests/Feature/AsistenciaControllerTest.php`. Cada modelo tiene su `Factory` en
  `database/factories/`.

## Modelo de datos — Grados, Secciones, Estudiantes, Horarios, Asistencias

- `grados`: `id`, `nombre` (string 50, **unique**), `estado` (bool, default true),
  timestamps.
- `horarios`: `id`, `nombre` (string 50, **unique**), `estado` (bool), `tolerancia`
  (unsignedSmallInteger, minutos de margen tras `hora_entrada` antes de marcar `TARDIA`),
  `hora_entrada`/`hora_salida` (time), timestamps. Sembrados por `HorarioSeeder`
  ("Matutino" 07:00–12:00, "Vespertino" 13:00–18:00, tolerancia 10 min).
- `secciones`: `id`, `nombre` (string 20), `grado_id` (FK → `grados`, `restrictOnDelete`,
  `cascadeOnUpdate`), `horario_id` (FK → `horarios`, **nullable**, `nullOnDelete`) — una
  sección sin horario asignado no puede generar QR de asistencia, ver más abajo —,
  `estado` (bool), timestamps. **Unique compuesto** `(grado_id, nombre)` — el nombre de
  sección es único solo dentro de su grado, no globalmente.
- `estudiantes`: `id`, `codigo_estudiante` (string 30, unique), `nombres`/`apellidos`
  (string 100), `qr_token` (uuid, **unique, not nullable** — lo genera el backend con
  `Str::uuid()` al crear, nunca lo envía el cliente ni se edita después), `seccion_id`
  (FK → `secciones`, `restrictOnDelete`, `cascadeOnUpdate`), `user_id` (FK → `users`,
  **nullable, unique**, `nullOnDelete` — vincula el login del alumno con su perfil;
  `EstudianteController::store()` crea el `User` con rol Alumno y contraseña temporal en la
  misma transacción), `estado` (bool), timestamps. No hay campos de fecha de nacimiento,
  DPI ni foto en el esquema actual.

  `qr_token` tiene su propio endpoint de consulta (`GET /v1/estudiantes/qr/{qr_token}`,
  permiso `estudiante.view`) pero **no se usa en el flujo de asistencia actual** (ver
  abajo) — queda reservado para un posible módulo futuro (p. ej. que alguien escanee el
  carnet del alumno). Si no se construye ese módulo, vale la pena revisar si conviene
  retirar el endpoint para no dejar superficie de API sin propósito claro.
- `asistencias`: `id`, `estudiante_id` (FK → `estudiantes`, `restrictOnDelete`,
  `cascadeOnUpdate`), `fecha_asistencia` (date), `hora_entrada` (time, nullable), `estado`
  (**enum** `PRESENTE`/`TARDIA`/`AUSENTE` — nótese que, a diferencia de las demás tablas,
  esta columna no es un booleano de borrado lógico sino el resultado del cálculo de
  puntualidad), `observaciones` (string 255, nullable), `registrado_por` (FK → `users`,
  nullable, `nullOnDelete` — quién marcó la asistencia), timestamps. **Unique compuesto**
  `(estudiante_id, fecha_asistencia)` — un estudiante solo puede tener una asistencia por
  día.

Jerarquía: `Grado hasMany Seccion`, `Horario hasMany Seccion`, `Seccion belongsTo
Grado/Horario` + `hasMany Estudiante`, `Estudiante belongsTo Seccion/User` + `hasMany
Asistencia`, `Asistencia belongsTo Estudiante` + `belongsTo User` (como `registradoPor`).

Modelo Eloquent `Administrador` **no existe todavía** (solo la migración `administradores`,
FK a `users`) — sigue fuera de alcance mientras no se necesite gestionarlos vía API.

## Módulo de Asistencia (QR de sección)

A diferencia de lo que podría sugerir el nombre `estudiantes.qr_token`, el flujo de marcar
asistencia **no** usa el QR individual del estudiante. Funciona al revés:

1. El docente/admin autenticado (permiso `asistencia.mark`) pide
   `GET /v1/asistencias/generar-qr/{seccion}`. `AsistenciaController::generarQr()` valida
   que la sección esté activa y tenga un `horario` activo asignado, cifra
   `{seccion_id, horario_id, fecha: hoy}` con `Crypt::encrypt()` y genera una imagen QR
   (`simplesoftwareio/simple-qrcode`) en base64 con ese payload cifrado.
2. El alumno autenticado (rol **Alumno** exacto — el controlador comprueba el nombre del
   rol, no solo el permiso) escanea ese QR y hace `POST /v1/asistencias/registrar` con
   `{"qr_token": "<payload cifrado>"}` (el nombre del campo es heredado, pero es el payload
   de la sección, no el `qr_token` del estudiante). `AsistenciaController::registrar()`
   descifra el payload, verifica que no haya expirado (debe ser la fecha de hoy) y que el
   estudiante autenticado (resuelto vía `estudiantes.user_id`) pertenezca a esa sección,
   calcula `PRESENTE`/`TARDIA`/`AUSENTE` comparando la hora actual contra
   `horario.hora_entrada + horario.tolerancia`, evita duplicados del mismo día (constraint
   único + chequeo explícito → 409) y persiste la fila en `asistencias` dentro de una
   transacción.
3. El alumno autenticado puede ver su propio historial con
   `GET /v1/asistencias/historial` (permiso `asistencia.view`, paginado vía `per_page`) —
   **self-view únicamente**, solo sus propias asistencias.
4. Docente/Admin (permiso `asistencia.report`) tienen `GET /v1/asistencias/reporte` para
   ver la asistencia de **todos** los estudiantes, con filtros opcionales `seccion_id`,
   `grado_id`, `estado` (`PRESENTE`/`TARDIA`/`AUSENTE`) y rango `fecha_desde`/`fecha_hasta`
   — también paginado. A diferencia de `historial()`, aquí `AsistenciaResource` sí incluye
   el `estudiante` anidado (con su `seccion.grado`) porque hace falta saber de quién es
   cada fila; `historial()` no hace eager load de esa relación así que ahí el campo queda
   ausente (mismo `AsistenciaResource`, comportamiento condicional vía `whenLoaded`).

`AsistenciaMock` (en `app/Http/Mock/`) son solo constantes de ejemplo para Swagger — no
indican que la lógica sea simulada; todo lo anterior persiste de verdad en MySQL/SQLite.

La rama remota `feature/reportes` (sin fusionar) **no se usó** para construir el reporte
agregado — revisando su contenido real, era solo una copia (con bugs) del self-view de
alumno, no un reporte por sección/grado. `asistencias/reporte` se escribió desde cero en
`feature/reporte-asistencia-docente`. Esa rama vieja puede cerrarse sin fusionar.

## Git

Cada módulo/feature se trabaja en su propia rama creada desde el branch base actualizado
(`feature/crud-grados`, `feature/crud-secciones`, `feature/crud-estudiantes`, ...). Orden
de trabajo de los CRUD: Grados → Secciones → Estudiantes, por la dependencia de llaves
foráneas.

**Ojo con `main` vs `master`**: el repo tiene ambas branches locales y remotas, pero
`main`/`origin/main` (el default branch configurado en GitHub) solo tiene el commit inicial
huérfano — todo el trabajo real (39 commits y contando) vive en `master`/`origin/master`.
Ramas nuevas deben crearse desde `master`, no desde `main`, hasta que el equipo decida
corregir el default branch en GitHub (no se tocó como parte de este trabajo, es una
decisión del equipo/infra compartida).

## Pendientes conocidos (fuera de alcance actual)

- Decidir si se expone borrado físico en el futuro (hoy es solo lógico vía `estado`).
- Modelo/controlador para `administradores` (no es responsabilidad de los módulos
  actuales). `roles`/`permissions` ya los implementó otro miembro del equipo.
- Decidir si el endpoint `GET /v1/estudiantes/qr/{qr_token}` se conecta a algún flujo real
  o se retira (hoy no lo usa el módulo de asistencia).
- **`generarQr()` requiere `ext-imagick`, no solo `ext-gd`** — pendiente de decisión en
  equipo (consultarlo antes de tocar código): `simplesoftwareio/simple-qrcode` 4.2.0 (la
  última versión del paquete) usa `ImagickImageBackEnd` para el formato `png`
  incondicionalmente (`Generator::getFormatter()`), sin importar si `gd` está disponible;
  `gd` solo lo usa para el `merge()` del logo y el degradado. El propio `composer.json` del
  paquete ya avisa esto (`ext-imagick` en `"suggest"`, nota "Allows the generation of PNG
  QrCodes"). Instalar Imagick+ImageMagick en Windows es frágil (el DLL de PHP tiene que
  calzar exacto con la versión nativa de ImageMagick instalada). Alternativa sin esa
  fragilidad: cambiar el formato a `svg` en `generarQr()` — no necesita `gd` ni `imagick`,
  mantiene el degradado, pero pierde el logo superpuesto (el paquete solo lo soporta en
  `png`, ver `Generator::generate()`) y cambia el mime type de la respuesta
  (`image/svg+xml` en vez de `image/png`) — el frontend lo seguiría pudiendo mostrar en un
  `<img>` sin problema, pero es un cambio de contrato que hay que avisar.
