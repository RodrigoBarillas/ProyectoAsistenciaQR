<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="280" alt="Laravel Logo">
</p>

<h1 align="center">ProyectoAsistenciaQR</h1>

<p align="center">
  API REST en Laravel para control de asistencia escolar mediante códigos QR.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-%5E8.3-777BB4?logo=php&logoColor=white" alt="PHP ^8.3">
  <img src="https://img.shields.io/badge/Auth-JWT-000000" alt="JWT Auth">
  <img src="https://img.shields.io/badge/DB-MySQL-4479A1?logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Tests-79%2F80-success" alt="Tests">
</p>

---

Este backend es consumido por un frontend separado (no vive en este repositorio). Expone
una API versionada (`/api/v1/...`) para gestionar grados, secciones, estudiantes y el
registro de asistencia por QR, con autenticación JWT y permisos granulares por rol.

## Tabla de contenidos

- [Stack](#stack)
- [Puesta en marcha local](#puesta-en-marcha-local)
- [Variables de entorno relevantes](#variables-de-entorno-relevantes)
- [Datos de prueba](#datos-de-prueba)
- [Documentación interactiva de la API](#documentación-interactiva-de-la-api)
- [Módulos y endpoints](#módulos-y-endpoints)
- [Cómo funciona el flujo de asistencia por QR](#cómo-funciona-el-flujo-de-asistencia-por-qr)
- [Testing](#testing)
- [Notas conocidas del entorno](#notas-conocidas-del-entorno)
- [Estructura del proyecto](#estructura-del-proyecto)

## Stack

| Área           | Tecnología                                                         |
| --------------- | ------------------------------------------------------------------- |
| Framework       | Laravel 13 (PHP ^8.3)                                               |
| Base de datos   | MySQL en contenedor Docker local (tests corren contra SQLite in-memory) |
| Autenticación   | JWT (`php-open-source-saver/jwt-auth`), guard `api`                 |
| Autorización    | Roles + permisos granulares (middleware `permission:<nombre>`)      |
| Documentación   | `laravel/swagger` con atributos PHP nativos (`#[SwaggerSection]`, etc.) |
| QR              | `simplesoftwareio/simple-qrcode` (requiere la extensión `gd`)        |

## Puesta en marcha local

```bash
# 1. Instalar dependencias
composer install

# 2. Configurar el entorno
cp .env.example .env
php artisan key:generate
php artisan jwt:secret --force

# 3. Levantar MySQL (contenedor Docker local, ver sección de variables de entorno)
#    y crear la base de datos a mano si es la primera vez:
#    CREATE DATABASE asistencia_qr;

# 4. Migrar y cargar datos de prueba
php artisan migrate
php artisan db:seed

# 5. Levantar el servidor
php artisan serve
```

La API queda disponible en `http://localhost:8000/api/v1/...`.

## Variables de entorno relevantes

Además de las variables estándar de Laravel, este proyecto usa:

| Variable                         | Descripción                                                        |
| --------------------------------- | -------------------------------------------------------------------- |
| `DB_CONNECTION` / `DB_HOST` / `DB_PORT` / `DB_DATABASE` | Apuntan al contenedor MySQL local (`asistencia_qr`, usuario `root`) |
| `JWT_SECRET`                     | Requerido por el guard `api`; generar con `php artisan jwt:secret --force` |
| `ASISTENCIA_HORA_INICIO`         | Hora de referencia por defecto para el cálculo de puntualidad       |
| `ASISTENCIA_TOLERANCIA_MINUTOS`  | Minutos de margen por defecto antes de marcar `TARDIA`               |

Las pruebas de feature (`php artisan test`) corren contra SQLite in-memory
(`phpunit.xml`), independiente de la base de datos de la app — no requiere el contenedor
de MySQL levantado.

## Datos de prueba

`php artisan db:seed` carga un escenario mínimo ya encadenado para probar el flujo
completo sin Tinker:

| Entidad     | Datos de ejemplo                                                              |
| ----------- | ------------------------------------------------------------------------------ |
| Horarios    | Matutino (07:00–12:00) y Vespertino (13:00–18:00), 10 min de tolerancia        |
| Grados      | Primero Básico, Segundo Básico                                                |
| Secciones   | Sección "A" por grado, vinculada al horario Matutino                          |
| Usuarios    | `admin@uped.edu.sv` / `password` (Administrador), 3 docentes de ejemplo       |
| Estudiantes | EST-001 a EST-003, cada uno con su propio usuario (p. ej. `ana.perez@uped.edu.sv` / `password`) |
| Asistencia  | Un registro `PRESENTE` de ejemplo para EST-001 (hoy)                          |

Todos los seeders son idempotentes (`updateOrCreate`/`firstOrNew`): correr
`php artisan db:seed` varias veces no duplica datos.

## Documentación interactiva de la API

La API se documenta con atributos PHP nativos sobre los controladores (no `@OA\` ni
`l5-swagger`). La UI de prueba queda servida en:

```
http://localhost:8000/docs
```

## Módulos y endpoints

Todas las rutas viven bajo `Route::prefix('v1')` y (salvo login) requieren JWT + el
permiso indicado.

| Módulo | Rutas principales | Permisos |
| ------ | ------------------ | -------- |
| Auth | `POST /auth/login`, `POST /auth/refresh`, `POST /auth/logout` | — |
| Roles / Permisos | `GET /roles`, `POST/PUT/DELETE /roles/{role}`, `GET /permissions` | `role.view`, `role.assign` |
| Grados | CRUD completo (`GET/POST /grados`, `GET/PUT/DELETE /grados/{grado}`) | `grado.view\|create\|edit\|delete` |
| Secciones | CRUD completo (`GET/POST /secciones`, `GET/PUT/DELETE /secciones/{seccion}`) | `seccion.view\|create\|edit\|delete` |
| Estudiantes | CRUD completo + `GET /estudiantes/qr/{qr_token}` | `estudiante.view\|create\|edit\|delete` |
| Asistencias | `GET /asistencias/generar-qr/{seccion}`, `POST /asistencias/registrar`, `GET /asistencias/historial`, `GET /asistencias/reporte` | `asistencia.mark`, `asistencia.view`, `asistencia.report` |

Los módulos CRUD (Grados/Secciones/Estudiantes) usan borrado lógico (`estado = false`),
nunca borrado físico.

## Cómo funciona el flujo de asistencia por QR

A diferencia de un QR individual por estudiante, el flujo funciona al revés:

1. El **docente/admin** pide `GET /asistencias/generar-qr/{seccion}` y obtiene una imagen
   QR (base64) con un payload cifrado (`seccion_id`, `horario_id`, fecha de hoy).
2. El **alumno** autenticado escanea ese QR y hace `POST /asistencias/registrar` con el
   payload. El backend valida que el QR no haya expirado, que el alumno pertenezca a esa
   sección, calcula `PRESENTE`/`TARDIA`/`AUSENTE` según el horario de la sección y
   persiste el registro (una asistencia por alumno por día).
3. El alumno puede revisar su propio historial con `GET /asistencias/historial`.
4. Docente/Admin pueden ver la asistencia de todos los estudiantes con
   `GET /asistencias/reporte`, filtrando por sección, grado, estado o rango de fechas.

Más detalle de las decisiones de diseño en [`CLAUDE.md`](CLAUDE.md).

## Testing

```bash
php artisan test
```

Corre contra SQLite in-memory, no requiere el contenedor de MySQL levantado. El test que
genera la imagen QR real se salta automáticamente si no están disponibles las extensiones
`gd` **y** `imagick` (ver nota abajo).

## Notas conocidas del entorno

- La generación de QR (`simplesoftwareio/simple-qrcode`) necesita **`gd` y `imagick`**,
  no solo `gd` — el paquete usa Imagick para renderizar PNG sin importar si `gd` está
  disponible (`gd` solo se usa para el logo/degradado superpuesto). Instalaciones
  minimalistas de PHP (p. ej. `php.new`/herd-lite) no traen ninguna de las dos por
  defecto. [Laravel Herd](https://herd.laravel.com) (la versión completa, no "lite") sí
  trae `gd` y `sodium` listos, pero **no `imagick`** — sigue pendiente una decisión de
  equipo entre instalar Imagick+ImageMagick (frágil en Windows) o cambiar `generarQr()` a
  formato `svg` (sin esa dependencia, pero sin el logo superpuesto). Ver detalle en
  `CLAUDE.md`.
- `laravel/sanctum` sigue instalado pero **no** es el mecanismo de autenticación activo
  (se usa JWT vía el guard `api`).

## Estructura del proyecto

```
app/
  Http/
    Controllers/     # un controlador por recurso
    Requests/         # FormRequest por operación de escritura
    Resources/        # JsonResource por recurso
    Mock/             # constantes de ejemplo para la documentación Swagger
  Models/             # modelos Eloquent (atributos PHP #[Fillable], relaciones explícitas)
routes/
  api.php             # todas las rutas, bajo /api/v1
database/
  migrations/         # grados, secciones, estudiantes, horarios, asistencias, ...
  factories/          # una factory por modelo, usadas en los tests
  seeders/            # datos de ejemplo encadenados (ver "Datos de prueba")
docs/
  PLAN-CRUDS.md       # historial detallado del diseño de los módulos CRUD
tests/
  Feature/            # un test de feature por controlador
```

Convenciones de código completas (modelos, validación, Swagger, borrado lógico, tests)
en [`CLAUDE.md`](CLAUDE.md).
