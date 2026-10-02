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

## 2. Módulo Grados — `feature/crud-grados` ✅ Terminado

> **Estado:** implementado, probado manualmente (éxito y error en los 5 endpoints) y
> documentado en Swagger. Pendiente de PR/merge a `main` (decisión del equipo).

### Notas de implementación (diferencias respecto al plan original)

- **Rama base real:** el repo remoto tiene `main` con un commit inicial vacío
  (`.gitignore` + `README.md`); todo el código Laravel vigente vive en `master`. Se creó
  `feature/crud-grados` desde `master` (confirmado con el usuario) en vez de `main`. Antes
  de ramificar se commitearon a `master` los cambios pendientes de `CLAUDE.md`, `AGENTS.md`
  y este plan.
- **Parámetros de ruta con tipo escalar en vez de route-model-binding:** el paquete
  `laravel/swagger` (`epmyas2022/laravel-swagger`) solo trata como "path parameter" a los
  argumentos del método cuyo tipo **no** es una clase existente (`class_exists($type)`
  falso). Si `show`/`update`/`destroy` se tipan con `Grado $grado` (binding implícito), el
  generador:
  - no documenta `{grado}` como parámetro de ruta (el `parameters` del path queda vacío), y
  - en `destroy` (sin body real) genera un `requestBody` falso apuntando a un schema vacío
    `Grado`.

  Por eso `GradoController::show/update/destroy` usan `string $grado` (igual que el patrón
  ya usado en `ExampleController::destroy(string $id)`) y resuelven el modelo manualmente
  con `Grado::findOrFail($grado)`. El comportamiento de cara al cliente no cambia:
  `ModelNotFoundException` sigue devolviendo 404 JSON vía `shouldRenderJsonWhen`. Esto
  aplica también a los módulos de Secciones y Estudiantes.
- **`estado` tras `create()`:** `Grado::create($request->validated())` no envía `estado`
  cuando el cliente lo omite, por lo que SQLite aplica el default `true` a nivel de
  columna — pero la instancia en memoria que devuelve `create()` no se refresca con ese
  valor (queda `null` hasta releer de BD). Se agregó `->refresh()` después de `create()` en
  `store()` para que la respuesta refleje el valor real persistido.
- **Swagger — una sola respuesta de ejemplo por endpoint:** el atributo
  `#[SwaggerResponse(...)]` no es repetible (PHP no permite aplicar el mismo atributo dos
  veces sin `Attribute::IS_REPEATABLE`), así que cada método documenta un único código de
  éxito con ejemplo (`200`/`201`). Los códigos de error (`422`, `404`) se describen en el
  texto de `#[SwaggerSummary(...)]`, ya que corresponden al formato de error estándar de
  Laravel (no a un schema particular del recurso).

### Archivos creados

| Archivo | Resultado |
|---|---|
| `app/Models/Grado.php` | Hecho, igual al plan (`#[Fillable(['nombre','estado'])]`, cast `estado` a boolean, `secciones(): HasMany`) |
| `app/Http/Controllers/GradoController.php` | Hecho; `show/update/destroy` usan `string $grado` + `findOrFail` (ver nota arriba) |
| `app/Http/Requests/StoreGradoRequest.php` | Hecho, igual al plan |
| `app/Http/Requests/UpdateGradoRequest.php` | Hecho, igual al plan (`Rule::unique('grados','nombre')->ignore($this->grado)`) |
| `app/Http/Resources/GradoResource.php` | Hecho (`id`, `nombre`, `estado`, `created_at`, `updated_at`) |

### Archivos modificados
- `routes/api.php`: agregado el grupo con middleware comentado y
  `Route::apiResource('grados', GradoController::class)`, igual al plan.

### Pruebas realizadas (manual, vía `curl` contra `php artisan serve`)
- `GET /api/grados` — lista vacía y lista con 2 registros → 200.
- `POST /api/grados` — creación sin `estado` (default `true`) y con `estado` explícito →
  201; sin `nombre` → 422; `nombre` duplicado → 422; `nombre` > 50 caracteres → 422.
- `GET /api/grados/{id}` — detalle existente → 200; id inexistente → 404.
- `PUT/PATCH /api/grados/{id}` — edición válida → 200; `nombre` duplicado contra otro
  grado → 422; mismo `nombre` propio (verifica `ignore()`) → 200; id inexistente → 404.
- `DELETE /api/grados/{id}` — inactivación (`estado=false`, fila persiste en BD) → 200; id
  inexistente → 404.
- Documentación verificada en `/api-docs` (JSON) y `/docs` (UI Swagger).

### Archivos a crear (plan original, referencia)
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

## 3. Módulo Secciones — `feature/crud-secciones` ✅ Terminado

> **Estado:** implementado, probado manualmente (éxito y error en los 5 endpoints, incluida
> la regla de unicidad compuesta y el filtro `?grado_id=`) y documentado en Swagger.
> Pendiente de PR/merge (decisión del equipo).

### Notas de implementación (diferencias respecto al plan original)

- **Rama base real:** igual que con Grados, `main` solo tiene el commit inicial vacío y
  el módulo de Grados (`feature/crud-grados`) todavía no está mergeado a `master`/`main`.
  Como Secciones depende del modelo `Grado`, `feature/crud-secciones` se creó a partir de
  `feature/crud-grados` (que ya contiene `master` actualizado + el módulo de Grados) en
  vez de `main`, para no tener que mergear nada a la rama principal.
- **`protected $table = 'secciones'`:** Eloquent infiere el nombre de tabla pluralizando
  en inglés el nombre de la clase (`Seccion` → `seccions`), lo que no coincide con la
  tabla real `secciones` y producía un `QueryException` (`no such table: seccions`) en
  todos los endpoints. Se agregó la propiedad `$table` explícita en el modelo. Esto no
  afecta a `Grado` (`Grado` → `Grados` sí coincide con la pluralización en inglés) pero
  **aplica también al modelo `Estudiante`** del próximo módulo si su plural en inglés no
  coincide con `estudiantes` (sí coincide, `Estudiante` → `Estudiantes`, así que no haría
  falta ahí, pero conviene revisarlo al implementarlo).
- **Parámetro de ruta `{seccione}` en vez de `{seccion}`:** `Route::apiResource` también
  pluraliza/singulariza en inglés para nombrar el parámetro de ruta; `secciones` singulariza
  a `seccione` (no `seccion`). Seguimos el mismo patrón que `GradoController` (parámetros
  escalares `string` en vez de route-model-binding, ver nota del módulo de Grados), pero
  el argumento del método se nombró `$seccione` (no `$seccion`) para que coincida
  exactamente con el nombre real del parámetro de ruta — así el body del método resuelve
  bien el valor y el paquete `laravel/swagger` documenta el path param con el nombre
  correcto (`/api/secciones/{seccione}` con parámetro `seccione`, verificado en
  `/api-docs`). En `UpdateSeccionRequest` esto implica usar `$this->route('seccione')`
  en vez del acceso mágico `$this->seccion` (que ya no resuelve a nada porque no hay
  ninguna clave `seccion` en la request ni en los parámetros de ruta).
- **Unicidad compuesta `(grado_id, nombre)`:** implementada con
  `Rule::unique('secciones')->where(fn ($q) => $q->where('grado_id', $this->grado_id))`,
  más `->ignore(...)` en `UpdateSeccionRequest`. Probado: mismo nombre en grados distintos
  se permite; mismo nombre repetido en el mismo grado da 422; editar una sección
  conservando su propio nombre no dispara el error (gracias a `ignore`).
- **`SeccionResource` incluye el grado relacionado:** se agregó `grado_id` (el escalar) y
  `grado` (objeto completo vía `GradoResource` + `whenLoaded`) en la respuesta. El
  controlador hace eager loading (`with('grado')` / `load('grado')`) en los 5 métodos para
  evitar N+1.
- **Filtro `?grado_id=` en `index`:** implementado con `when($request->filled('grado_id'), ...)`
  sobre el query builder; no hay atributo Swagger dedicado a documentar query params en el
  paquete `laravel/swagger` (solo path params y request body), así que el filtro se
  describe en el texto de `#[SwaggerSummary(...)]`, igual que los códigos de error en el
  módulo de Grados.

### Archivos creados

| Archivo | Resultado |
|---|---|
| `app/Models/Seccion.php` | `#[Fillable(['nombre','grado_id','estado'])]`, `$table = 'secciones'` (ver nota arriba), cast `estado` a boolean, `grado(): BelongsTo`, `estudiantes(): HasMany` |
| `app/Http/Controllers/SeccionController.php` | CRUD completo; `show/update/destroy` usan `string $seccione` (ver nota arriba); `index` con filtro `?grado_id=` y eager loading |
| `app/Http/Requests/StoreSeccionRequest.php` | `nombre` + unique compuesto con `grado_id`, `grado_id` + `exists:grados,id`, `estado` opcional |
| `app/Http/Requests/UpdateSeccionRequest.php` | Igual que Store, con `Rule::unique(...)->ignore($this->route('seccione'))` |
| `app/Http/Resources/SeccionResource.php` | `id`, `nombre`, `grado_id`, `grado` (anidado), `estado`, timestamps |

### Archivos modificados
- `routes/api.php`: agregado `Route::apiResource('secciones', SeccionController::class)`
  dentro del mismo grupo (middleware comentado) que `grados`.
- `app/Models/Grado.php`: sin cambios — `secciones(): HasMany` ya existía desde el módulo
  anterior.

### Pruebas realizadas (manual, vía `curl` contra `php artisan serve`)
- `GET /api/secciones` — lista vacía y con 2+ registros → 200; con `?grado_id=`, filtra
  correctamente.
- `POST /api/secciones` — creación válida → 201 (incluye el grado anidado); `nombre`
  repetido en el mismo `grado_id` → 422; mismo `nombre` en `grado_id` distinto → 201
  (permitido); sin `nombre` → 422; `nombre` > 20 caracteres → 422; `grado_id` inexistente
  → 422; `grado_id` faltante → 422.
- `GET /api/secciones/{id}` — detalle existente → 200; id inexistente → 404.
- `PUT /api/secciones/{id}` — edición válida → 200; conservar el propio `nombre` (verifica
  `ignore()`) → 200; renombrar a un `nombre` ya usado en el mismo grado → 422; id
  inexistente → 404.
- `DELETE /api/secciones/{id}` — inactivación (`estado=false`, fila persiste) → 200; id
  inexistente → 404.
- Documentación verificada en `/api-docs` (JSON: path `/api/secciones/{seccione}` con
  parámetro `seccione` correctamente emparejado, schemas de `Store`/`UpdateSeccionRequest`
  con `nombre`/`grado_id`/`estado`) y en `/docs` (UI Swagger, carga 200).

### Archivos a crear (plan original, referencia)
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

## 4. Módulo Estudiantes — `feature/crud-estudiantes` ✅ Terminado

> **Estado:** implementado, probado manualmente (éxito y error en los 5 endpoints,
> incluida la inmutabilidad de `qr_token` y el filtro `?seccion_id=`) y documentado en
> Swagger. Pendiente de PR/merge (decisión del equipo).

### Notas de implementación (diferencias respecto al plan original)

- **Rama base real:** igual que con Secciones, se creó `feature/crud-estudiantes` a
  partir de `feature/crud-secciones` (que ya contiene `master` actualizado + los módulos
  de Grados y Secciones) en vez de `main`, porque `main` solo tiene el commit inicial
  vacío y Estudiantes depende del modelo `Seccion`.
- **Sin necesidad de `$table` explícito ni de renombrar el parámetro de ruta:** a
  diferencia de `Seccion` (ver nota del módulo de Secciones), `Str::plural(Str::snake('Estudiante'))`
  da `estudiantes` (coincide con la tabla real) y `Str::singular('estudiantes')` da
  `estudiante` (coincide con el nombre natural del recurso). Verificado con
  `php -r "echo Illuminate\Support\Str::singular('estudiantes');"` antes de implementar.
  Por eso el parámetro de ruta es simplemente `{estudiante}` (no un nombre distinto como
  pasó con `seccione`), aunque se mantiene el mismo patrón de `show/update/destroy` con
  `string $estudiante` + `Estudiante::findOrFail($estudiante)` en vez de route-model-binding,
  por la misma limitación de `laravel/swagger` documentada en el módulo de Grados.
- **Generación de `qr_token` sin mass assignment:** `qr_token` **no** está en el array de
  `#[Fillable([...])]` del modelo (no es un campo de entrada del cliente), así que
  `Estudiante::create($request->validated())` lo dejaría fuera del `INSERT` y violaría la
  restricción `NOT NULL` de la columna. `store()` resuelve esto con
  `new Estudiante($request->validated())`, asigna `$estudiante->qr_token = Str::uuid();`
  como propiedad directa (no sujeta a la protección de mass assignment) y recién entonces
  llama a `->save()`, de modo que el `INSERT` incluye el UUID en una sola operación.
- **`qr_token` inmutable en `update()`:** no fue necesario ningún filtro adicional —
  `UpdateEstudianteRequest::rules()` no declara `qr_token`, así que `$request->validated()`
  nunca lo incluye aunque el cliente lo envíe en el body. Probado enviando un `qr_token`
  arbitrario en `PUT`: la respuesta conserva el UUID original.
- **`EstudianteResource` incluye la sección y el grado anidados:** `seccion_id` (escalar) y
  `seccion` (objeto vía `SeccionResource`, que a su vez anida `grado` vía `GradoResource`).
  El controlador hace eager loading (`with('seccion.grado')` / `load('seccion.grado')`) en
  los 5 métodos para evitar N+1.
- **Filtro `?seccion_id=` en `index`:** mismo patrón que el filtro `?grado_id=` de
  Secciones (`when($request->filled('seccion_id'), ...)`), documentado en el texto de
  `#[SwaggerSummary(...)]` por la misma limitación del paquete `laravel/swagger` (no
  documenta query params, solo path params y request body).

### Archivos creados

| Archivo | Resultado |
|---|---|
| `app/Models/Estudiante.php` | `#[Fillable(['codigo_estudiante','nombres','apellidos','seccion_id','estado'])]` (sin `qr_token`, ver nota arriba), cast `estado` a boolean, `seccion(): BelongsTo` |
| `app/Http/Controllers/EstudianteController.php` | CRUD completo; `show/update/destroy` usan `string $estudiante` + `findOrFail`; `index` con filtro `?seccion_id=` y eager loading `seccion.grado`; `store` genera `qr_token` antes de `save()` |
| `app/Http/Requests/StoreEstudianteRequest.php` | `codigo_estudiante` único, `nombres`/`apellidos` requeridos, `seccion_id` + `exists:secciones,id`, `estado` opcional |
| `app/Http/Requests/UpdateEstudianteRequest.php` | Igual que Store, con `Rule::unique('estudiantes','codigo_estudiante')->ignore($this->route('estudiante'))` |
| `app/Http/Resources/EstudianteResource.php` | `id`, `codigo_estudiante`, `nombres`, `apellidos`, `qr_token`, `seccion_id`, `seccion` (anidada), `estado`, timestamps |

### Archivos modificados
- `routes/api.php`: agregado `Route::apiResource('estudiantes', EstudianteController::class)`
  dentro del mismo grupo (middleware comentado) que `grados`/`secciones`.
- `app/Models/Seccion.php`: sin cambios — `estudiantes(): HasMany` ya existía desde el
  módulo anterior.

### Pruebas realizadas (manual, vía `curl` contra `php artisan serve`)
- `GET /api/estudiantes` — lista vacía y con 2+ registros → 200; con `?seccion_id=`,
  filtra correctamente (verificado con estudiantes en secciones distintas).
- `POST /api/estudiantes` — creación válida → 201 (incluye `qr_token` UUID y la sección/grado
  anidados); sin `codigo_estudiante` → 422; `codigo_estudiante` duplicado → 422;
  `seccion_id` inexistente → 422; `nombres` > 100 caracteres → 422; enviando un `qr_token`
  propio en el body → 201 y se ignora (se genera uno nuevo por el backend).
- `GET /api/estudiantes/{id}` — detalle existente → 200; id inexistente → 404.
- `PUT /api/estudiantes/{id}` — edición válida → 200; conservar el propio
  `codigo_estudiante` (verifica `ignore()`) → 200; renombrar a un `codigo_estudiante` ya
  usado por otro estudiante → 422; enviando un `qr_token` distinto → 200 con el `qr_token`
  original sin cambios; id inexistente → 404.
- `DELETE /api/estudiantes/{id}` — inactivación (`estado=false`, fila persiste) → 200; id
  inexistente → 404.
- Documentación verificada en `/api-docs` (JSON: paths `/api/estudiantes` y
  `/api/estudiantes/{estudiante}` con parámetro `estudiante`, schemas de
  `Store`/`UpdateEstudianteRequest`) y en `/docs` (UI Swagger, carga 200).

### Archivos a crear (plan original, referencia)
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
