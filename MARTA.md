# Persona 1 — Categorías y Preguntas

Te encargas de los **Puntos 1 y 2** del enunciado: gestión completa (CRUD) de **Categorías** y **Preguntas** tanto en **BankApi** (REST) como en **Ludiweb** (UI).

> Las migraciones de `categories` y `questions` ya están creadas y ejecutadas. No las modifiques sin avisar (tocan FK con `answers` y `game_questions`).

---

## 1. BankApi — Modelos

Crear en `BankApi/app/Models/`:

### `Category.php`
- `protected $fillable = ['nombre', 'descripcion'];`
- Relación `questions()` → `hasMany(Question::class)`.

### `Question.php`
- `protected $fillable = ['category_id', 'enunciado', 'dificultad', 'imagen'];`
- Relación `category()` → `belongsTo(Category::class)`.
- Relación `answers()` → `hasMany(Answer::class)` (la creará Persona 2, deja la línea preparada).

Comando: `php artisan make:model Category` y `php artisan make:model Question`.

---

## 2. BankApi — Form Requests (validación)

`php artisan make:request StoreCategoryRequest` y un `UpdateCategoryRequest` (igual). Lo mismo para Question.

### `StoreCategoryRequest`
```php
public function rules(): array {
    return [
        'nombre'      => ['required', 'string', 'max:100'],
        'descripcion' => ['nullable', 'string'],
    ];
}
public function authorize(): bool { return true; }
```

### `StoreQuestionRequest`
```php
public function rules(): array {
    return [
        'category_id' => ['required', 'exists:categories,id'],
        'enunciado'   => ['required', 'string'],
        'dificultad'  => ['required', 'in:easy,medium,hard'],
        'imagen'      => ['nullable', 'string', 'max:255'],
    ];
}
```

Para `Update*Request` cambia `required` por `sometimes` donde tenga sentido.

---

## 3. BankApi — API Resources

`php artisan make:resource CategoryResource` y `QuestionResource`. Devuelven JSON limpio:

```php
// CategoryResource
return [
    'id' => $this->id,
    'nombre' => $this->nombre,
    'descripcion' => $this->descripcion,
];
```

```php
// QuestionResource
return [
    'id' => $this->id,
    'category_id' => $this->category_id,
    'enunciado' => $this->enunciado,
    'dificultad' => $this->dificultad,
    'imagen' => $this->imagen,
    'category' => CategoryResource::make($this->whenLoaded('category')),
];
```

---

## 4. BankApi — Controladores

```bash
php artisan make:controller Api/CategoryController --api --model=Category
php artisan make:controller Api/QuestionController --api --model=Question
```

Implementar los 5 métodos de cada uno (`index, store, show, update, destroy`) usando los Form Request y los Resource.

`QuestionController@index` debería aceptar filtros opcionales por query string:
- `?category_id=3`
- `?dificultad=easy`

---

## 5. BankApi — Rutas

En [BankApi/routes/api.php](BankApi/routes/api.php), descomenta las dos líneas marcadas como **Persona 1** dentro del grupo `auth:sanctum`:

```php
Route::apiResource('categories', CategoryController::class);
Route::apiResource('questions',  QuestionController::class);
```

Y añade los `use` correspondientes arriba del archivo.

---

## 6. Ludiweb — Vistas

Crea en `LudiWeb/resources/views/`:

### `categories/index.blade.php`
Lista de categorías + botón "Nueva". Carga con `api.get('/categories')`.

### `categories/create.blade.php` y `categories/edit.blade.php`
Formulario con `nombre` y `descripcion`. Envía con `api.post('/categories', ...)` o `api.put('/categories/{id}', ...)`.

### `questions/index.blade.php`
Lista de preguntas con filtro por categoría y dificultad.

### `questions/create.blade.php` y `questions/edit.blade.php`
Formulario con desplegable de categoría (cargado desde `/categories`), enunciado y dificultad.

Todas las vistas usan `@extends('layouts.app')` y dentro de `@push('scripts')` ponen el JS que llama al `api`.

### Ejemplo `categories/index.blade.php`

```blade
@extends('layouts.app')
@section('title', 'Categorías')
@section('content')
    <h2>Categorías</h2>
    <p><a href="/categories/create">Nueva categoría</a></p>
    <table>
        <thead><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th></th></tr></thead>
        <tbody id="rows"><tr><td colspan="4">Cargando…</td></tr></tbody>
    </table>
@endsection
@push('scripts')
<script>
(async () => {
    try {
        const data = await api.get('/categories');
        const rows = document.getElementById('rows');
        rows.innerHTML = (data.data || data).map(c => `
            <tr>
                <td>${c.id}</td>
                <td>${c.nombre}</td>
                <td>${c.descripcion || ''}</td>
                <td>
                    <a href="/categories/${c.id}/edit">Editar</a>
                    <button onclick="del(${c.id})">Borrar</button>
                </td>
            </tr>`).join('');
    } catch (e) { document.getElementById('rows').innerHTML = `<tr><td colspan="4" class="error">${e.message}</td></tr>`; }
})();
async function del(id) {
    if (!confirm('¿Borrar?')) return;
    await api.del('/categories/' + id);
    location.reload();
}
</script>
@endpush
```

Aplica el mismo patrón a las vistas de preguntas.

---

## 7. Rutas Ludiweb

Descomenta las líneas marcadas como **Persona 1** en [LudiWeb/routes/web.php](LudiWeb/routes/web.php).

---

## 8. Pruebas

- Colección **Postman** con los 10 endpoints (5 de cada). Guárdala en `BankApi/postman/Persona1.json` (crea la carpeta).
- Probar a mano que cada formulario de Ludiweb funciona contra la API real.

---

## Checklist de entrega

- [ ] Modelos `Category` y `Question` con relaciones.
- [ ] Form Requests `Store/Update` para ambos.
- [ ] Resources `CategoryResource` y `QuestionResource`.
- [ ] Controladores `Api/CategoryController` y `Api/QuestionController`.
- [ ] Rutas activas en `routes/api.php`.
- [ ] Vistas Ludiweb (index/create/edit) para ambos.
- [ ] Filtro `?category_id=` y `?dificultad=` en `/questions`.
- [ ] Colección Postman.
- [ ] Probado de extremo a extremo en navegador.

---

## Bloqueos / dependencias

- **Necesitas**: que Persona 2 termine `auth` para poder probar con un usuario logueado. Mientras tanto, puedes eliminar el `auth:sanctum` temporalmente en `routes/api.php` para tus pruebas (¡acuérdate de devolverlo!).
- **Te bloquean**: nada, puedes empezar inmediatamente.
- **Bloqueas a**: Persona 2 (necesita `Question` para crear `Answer`) y Persona 3 (necesita ambos modelos).
