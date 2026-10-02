# Plan de implementación — CRUDs de Grados, Secciones y Estudiantes

> Alcance de esta etapa: solo CRUD vía API REST, sin autenticación/autorización activa
> (pero con la estructura lista para añadirla), documentados y probables desde Swagger.
> Orden de trabajo: **Grados → Secciones → Estudiantes**, cada uno en su propia rama
> (`feature/crud-grados`, `feature/crud-secciones`, `feature/crud-estudiantes`) creada
> desde `main` actualizado.

## 0. Contexto del proyecto (ver también `CLAUDE.md`)

- Laravel 13 / PHP 8.3+, SQLite por defecto.
- Las tablas `grados`, `secciones`, `estudiantes` ya existen (migraciones hechas por otro
  miembro del equipo). **No existen modelos Eloquent, controladores ni rutas** para ellas.
- Documentación de API con el paquete `laravel/swagger` (`epmyas2022/laravel-swagger`),
  no `l5-swagger`. Se documenta con atributos PHP (`#[SwaggerSection(...)]`, etc.) sobre
  el controlador; ver `app/Http/Controllers/ExampleController.php` como plantilla.
- No existe ninguna convención previa de API Resources, FormRequests, ni formato de
  respuesta/error — se define aquí, para que los 3 módulos sean consistentes entre sí.

## 1. Convenciones transversales (aplican a los 3 módulos)

### Modelos (`app/Models/`)
- Mismo estilo de atributos PHP que `App\Models\User`: `#[Fillable([...])]` en vez de
  `protected $fillable`.
- Cast de `estado` a `boolean` vía el método `casts()`.
- Relaciones Eloquent explícitas (ver detalle por módulo abajo).

### Validación (`app/Http/Requests/`)
- Un `FormRequest` por operación de escritura: `Store{Recurso}Request`,
  `Update{Recurso}Request`.
- `authorize()` devuelve `true` por ahora, con un comentario
  `// TODO: restringir cuando se agregue autenticación/roles`.

### Respuestas (`app/Http/Resources/`)
- Un `JsonResource` por recurso (`{Recurso}Resource`) para controlar exactamente qué
  campos se exponen (p. ej. ocultar columnas internas si se agregan en el futuro).
- Colecciones: usar el wrapping por defecto de Laravel (`data: [...]`) para `index`.
- Errores: se usan las respuestas estándar de Laravel — 422 (`ValidationException`, ya
  formateado por el framework), 404 (`ModelNotFoundException` en rutas con
  route-model-binding, ya en JSON por `shouldRenderJsonWhen` en `bootstrap/app.php`). No
  se necesita un manejador de excepciones custom para esta etapa.

### Borrado lógico (decisión confirmada con el usuario)
Las 3 tablas tienen una columna `estado` (boolean) y claves foráneas con
`restrictOnDelete()`. Para evitar conflictos de integridad referencial y preservar
historial (sobre todo de asistencia), **el método `destroy()` no borra la fila**: pone
`estado = false` y devuelve 200 con el recurso actualizado. No se expone borrado físico
en esta etapa.

### Preparación para autenticación (sin activarla)
En `routes/api.php`, las rutas de los 3 módulos se agrupan así, dejando el middleware
comentado y listo para activarse cuando el equipo implemente auth:

```php
Route::group([
    // 'middleware' => ['auth:sanctum'], // TODO: activar cuando se implemente autenticación
], function () {
    Route::apiResource('grados', GradoController::class);
    Route::apiResource('secciones', SeccionController::class);
    Route::apiResource('estudiantes', EstudianteController::class);
});
```

Sin prefijo de versión (`/api/grados`, no `/api/v1/grados`), consistente con la única
convención existente (`Route::apiResource('example', ExampleController::class)`).

### Documentación Swagger
- Un `#[SwaggerSection('Grados'|'Secciones'|'Estudiantes')]` por controlador.
- `#[SwaggerSummary('...')]` por método describiendo la acción.
- Tipar `store`/`update` con el `FormRequest` correspondiente en la firma del método para
  que el paquete infiera el cuerpo de la petición automáticamente (igual que se infieren
  los parámetros de ruta desde el type-hint del modelo).
- UI de prueba disponible en `/docs` (ya configurado, assets en `public/swagger/`).

---

## 2. Módulo Grados — `feature/crud-grados`

### Archivos a crear
| Archivo | Propósito |
|---|---|
| `app/Models/Grado.php` | Modelo Eloquent, relación `hasMany(Seccion::class)` |
| `app/Http/Controllers/GradoController.php` | CRUD, `#[SwaggerSection('Grados')]` |
| `app/Http/Requests/StoreGradoRequest.php` | Validación de creación |
| `app/Http/Requests/UpdateGradoRequest.php` | Validación de edición |
| `app/Http/Resources/GradoResource.php` | Forma de la respuesta JSON |

### Archivos a modificar
- `routes/api.php`: agregar `Route::apiResource('grados', GradoController::class)` dentro
  del grupo descrito arriba.

### Endpoints
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/grados` | Lista paginada de grados |
| POST | `/api/grados` | Crea un grado |
| GET | `/api/grados/{grado}` | Detalle de un grado |
| PUT/PATCH | `/api/grados/{grado}` | Edita un grado |
| DELETE | `/api/grados/{grado}` | Inactiva (`estado = false`) |

### Validaciones y reglas de negocio
- `nombre`: `required|string|max:50|unique:grados,nombre` (en update, ignorar el propio
  id con `Rule::unique('grados', 'nombre')->ignore($this->grado)`).
- `estado`: `sometimes|boolean` (default `true` al crear si no se envía).

### Pruebas
- Desde `/docs`: crear 2-3 grados, listar, ver detalle, editar nombre, intentar crear un
  duplicado (debe dar 422), inactivar uno (`DELETE`) y confirmar que `estado` queda en
  `false` pero la fila sigue en la BD.
- Alternativa: colección de Postman/Insomnia o `curl` contra `http://127.0.0.1:8000/api/grados`.

---

## 3. Módulo Secciones — `feature/crud-secciones`

### Archivos a crear
| Archivo | Propósito |
|---|---|
| `app/Models/Seccion.php` | `belongsTo(Grado::class)`, `hasMany(Estudiante::class)` |
| `app/Http/Controllers/SeccionController.php` | CRUD, `#[SwaggerSection('Secciones')]` |
| `app/Http/Requests/StoreSeccionRequest.php` | Validación de creación |
| `app/Http/Requests/UpdateSeccionRequest.php` | Validación de edición |
| `app/Http/Resources/SeccionResource.php` | Incluye el grado relacionado (`GradoResource`) |

### Archivos a modificar
- `routes/api.php`: agregar `Route::apiResource('secciones', SeccionController::class)`.
- `app/Models/Grado.php`: ya debe tener `secciones()` desde el módulo anterior.

### Endpoints
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/secciones` | Lista secciones (filtrable por `?grado_id=`) |
| POST | `/api/secciones` | Crea una sección |
| GET | `/api/secciones/{seccion}` | Detalle |
| PUT/PATCH | `/api/secciones/{seccion}` | Edita |
| DELETE | `/api/secciones/{seccion}` | Inactiva (`estado = false`) |

### Validaciones y reglas de negocio
- `nombre`: `required|string|max:20`.
- `grado_id`: `required|integer|exists:grados,id`.
- **Unique compuesto** `(grado_id, nombre)` — el mismo nombre de sección puede repetirse
  en grados distintos, pero no dentro del mismo grado:
  ```php
  Rule::unique('secciones')->where(fn ($q) => $q->where('grado_id', $this->grado_id))
      ->ignore($this->seccion ?? null),
  ```
- `estado`: `sometimes|boolean`.

### Pruebas
- Crear la sección "A" en el Grado 1 y la sección "A" en el Grado 2 → ambas deben
  permitirse.
- Intentar crear la sección "A" dos veces en el mismo grado → debe rechazarse (422).
- Filtrar por `?grado_id=` y confirmar que solo devuelve las secciones de ese grado.

---

## 4. Módulo Estudiantes — `feature/crud-estudiantes`

### Archivos a crear
| Archivo | Propósito |
|---|---|
| `app/Models/Estudiante.php` | `belongsTo(Seccion::class)` |
| `app/Http/Controllers/EstudianteController.php` | CRUD, `#[SwaggerSection('Estudiantes')]` |
| `app/Http/Requests/StoreEstudianteRequest.php` | Validación de creación |
| `app/Http/Requests/UpdateEstudianteRequest.php` | Validación de edición |
| `app/Http/Resources/EstudianteResource.php` | Incluye `qr_token` y la sección relacionada |

### Archivos a modificar
- `routes/api.php`: agregar `Route::apiResource('estudiantes', EstudianteController::class)`.
- `app/Models/Seccion.php`: ya debe tener `estudiantes()` desde el módulo anterior.

### Endpoints
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/estudiantes` | Lista estudiantes (filtrable por `?seccion_id=`) |
| POST | `/api/estudiantes` | Crea un estudiante (genera `qr_token` automáticamente) |
| GET | `/api/estudiantes/{estudiante}` | Detalle |
| PUT/PATCH | `/api/estudiantes/{estudiante}` | Edita (no permite modificar `qr_token`) |
| DELETE | `/api/estudiantes/{estudiante}` | Inactiva (`estado = false`) |

### Validaciones y reglas de negocio
- `codigo_estudiante`: `required|string|max:30|unique:estudiantes,codigo_estudiante`
  (ignorar propio id en update).
- `nombres`: `required|string|max:100`.
- `apellidos`: `required|string|max:100`.
- `seccion_id`: `required|integer|exists:secciones,id`.
- `estado`: `sometimes|boolean`.
- `qr_token`: **no es un campo de entrada** — no se valida ni se acepta del cliente. El
  controlador lo genera con `Str::uuid()` antes de persistir en `store()`, y lo devuelve
  en la respuesta para que el frontend genere la imagen QR. En `update()` nunca se
  modifica.

### Pruebas
- Crear un estudiante y confirmar que la respuesta incluye un `qr_token` con formato
  UUID válido.
- Editar el estudiante enviando un `qr_token` distinto en el body → confirmar que se
  ignora y el valor original no cambia.
- Crear dos estudiantes con el mismo `codigo_estudiante` → debe rechazarse (422).
- Filtrar por `?seccion_id=` y confirmar el listado correcto.

---

## 5. Flujo de Git

```bash
git checkout main
git pull
git checkout -b feature/crud-grados
# commits del módulo de Grados...
# PR: feature/crud-grados -> main

git checkout main
git pull
git checkout -b feature/crud-secciones
# commits del módulo de Secciones...
# PR: feature/crud-secciones -> main

git checkout main
git pull
git checkout -b feature/crud-estudiantes
# commits del módulo de Estudiantes...
# PR: feature/crud-estudiantes -> main
```

Cada rama se crea desde `main` ya actualizado con el módulo anterior fusionado, respetando
la dependencia de claves foráneas (Grado → Sección → Estudiante).

## 6. Pendientes fuera de alcance de esta etapa

- Autenticación/autorización real (Sanctum ya está instalado, solo falta activarlo y
  definir roles/políticas).
- Borrado físico y manejo de conflictos 409 (si se decide exponerlo más adelante).
- Endpoint de regeneración de `qr_token` (p. ej. carnet perdido) — no solicitado todavía.
- Tests automatizados (Feature tests de Pest/PHPUnit) — se sugieren en cada módulo pero no
  son obligatorios para esta etapa; se recomienda agregarlos si el tiempo lo permite.
