# Marta — Preguntas y Respuestas (Persona 1)

Te encargas de los **Puntos 2 y 3** del enunciado: gestión completa (CRUD) de **Preguntas** y **Respuestas** tanto en **BankApi** (REST) como en **Ludiweb** (UI). Validación clave: **3 respuestas por pregunta, 1 correcta**.

> Las migraciones de `questions` y `answers` ya están creadas y ejecutadas. No las modifiques sin avisar (tocan FK con `categories` y `game_questions`).

---

## 1. BankApi — Modelos

```bash
php artisan make:model Question
php artisan make:model Answer
```

### `Question.php`
```php
protected $fillable = ['category_id', 'enunciado', 'dificultad', 'imagen'];
public function category() { return $this->belongsTo(Category::class); }
public function answers()  { return $this->hasMany(Answer::class); }
```

### `Answer.php`
```php
protected $fillable = ['question_id', 'texto', 'es_correcta'];
protected $casts = ['es_correcta' => 'boolean'];
public function question() { return $this->belongsTo(Question::class); }
```

> El modelo `Category` lo crea Nico. Mientras no exista, puedes insertar categorías de prueba a mano por SQL en `database/database.sqlite`, o esperar a que Nico mergee.

---

## 2. BankApi — Form Requests

```bash
php artisan make:request StoreQuestionRequest
php artisan make:request UpdateQuestionRequest
php artisan make:request StoreAnswerRequest
php artisan make:request UpdateAnswerRequest
```

### `StoreQuestionRequest`
```php
public function authorize(): bool { return true; }
public function rules(): array {
    return [
        'category_id' => ['required', 'exists:categories,id'],
        'enunciado'   => ['required', 'string'],
        'dificultad'  => ['required', 'in:easy,medium,hard'],
        'imagen'      => ['nullable', 'string', 'max:255'],
    ];
}
```

`Update*Request` igual pero con `sometimes` donde proceda.

### `StoreAnswerRequest`
```php
public function authorize(): bool { return true; }
public function rules(): array {
    return [
        'texto'       => ['required', 'string'],
        'es_correcta' => ['required', 'boolean'],
    ];
}
```

---

## 3. BankApi — Validación 3/1 (regla del enunciado)

Esto va en `AnswerController@store` y `@update` (no se puede expresar como rule estándar):

```php
// store()
$question = Question::findOrFail($questionId);
if ($question->answers()->count() >= 3) {
    return response()->json(['message' => 'La pregunta ya tiene 3 respuestas.'], 422);
}
if ($request->es_correcta && $question->answers()->where('es_correcta', true)->exists()) {
    return response()->json(['message' => 'Ya existe una respuesta correcta.'], 422);
}
```

En `update()`, la misma comprobación cuando se cambia `es_correcta` a `true`.

---

## 4. BankApi — Resources

```bash
php artisan make:resource QuestionResource
php artisan make:resource AnswerResource
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
    'answers'  => AnswerResource::collection($this->whenLoaded('answers')),
];

// AnswerResource
return [
    'id' => $this->id,
    'question_id' => $this->question_id,
    'texto' => $this->texto,
    'es_correcta' => $this->es_correcta,
];
```

> ⚠️ **Coordinación con Mar**: cuando se devuelven preguntas durante una partida, NO debe verse `es_correcta`. Hablad si lo hacéis con un `AnswerPublicResource` (sin el campo) o con un flag. Recomendado: crear `AnswerPublicResource` aparte.

---

## 5. BankApi — Controladores

```bash
php artisan make:controller Api/QuestionController --api --model=Question
php artisan make:controller Api/AnswerController --api --model=Answer
```

`QuestionController@index` con filtros opcionales por query string:
- `?category_id=3`
- `?dificultad=easy`

```php
$query = Question::query();
if ($r->filled('category_id')) $query->where('category_id', $r->category_id);
if ($r->filled('dificultad'))  $query->where('dificultad',  $r->dificultad);
return QuestionResource::collection($query->with('answers')->get());
```

`AnswerController` recibe la `Question` por route binding (rutas anidadas).

---

## 6. BankApi — Rutas

En [BankApi/routes/api.php](BankApi/routes/api.php), descomenta tus líneas (marcadas como **Marta**) dentro del grupo `auth:sanctum`:

```php
Route::apiResource('questions',         QuestionController::class);
Route::apiResource('questions.answers', AnswerController::class)->shallow();
```

`shallow()` hace que `show/update/destroy` de `Answer` queden como `/answers/{answer}` (sin pasar por `/questions/{x}/answers/{y}`).

Añade los `use` arriba.

---

## 7. Ludiweb — Vistas

Crea en `LudiWeb/resources/views/`:

### Preguntas
- `questions/index.blade.php` — tabla con filtro por categoría y dificultad.
- `questions/create.blade.php` — form con desplegable de categoría (carga `/categories`), enunciado y dificultad.
- `questions/edit.blade.php` — igual pero precarga datos.

### Respuestas
- `answers/index.blade.php` — dada una pregunta (id por URL), lista sus respuestas, permite añadir hasta 3, marcar/desmarcar correcta y borrar.

### Ejemplo `questions/index.blade.php`
```blade
@extends('layouts.app')
@section('title', 'Preguntas')
@section('content')
<h2>Preguntas</h2>
<form id="filtros" class="row">
    <select name="category_id"><option value="">— Todas —</option></select>
    <select name="dificultad">
        <option value="">— Cualquier dificultad —</option>
        <option value="easy">easy</option>
        <option value="medium">medium</option>
        <option value="hard">hard</option>
    </select>
    <button>Filtrar</button>
    <a href="/questions/create">Nueva</a>
</form>
<table>
    <thead><tr><th>ID</th><th>Enunciado</th><th>Dificultad</th><th>Categoría</th><th></th></tr></thead>
    <tbody id="rows"><tr><td colspan="5">Cargando…</td></tr></tbody>
</table>
@endsection
@push('scripts')
<script>
async function loadCats() {
    const cats = await api.get('/categories');
    const sel = document.querySelector('[name=category_id]');
    (cats.data || cats).forEach(c => sel.add(new Option(c.nombre, c.id)));
}
async function loadQs() {
    const params = new URLSearchParams(new FormData(document.getElementById('filtros'))).toString();
    const data = await api.get('/questions' + (params ? '?' + params : ''));
    document.getElementById('rows').innerHTML = (data.data || data).map(q => `
        <tr>
            <td>${q.id}</td><td>${q.enunciado}</td><td>${q.dificultad}</td>
            <td>${q.category ? q.category.nombre : q.category_id}</td>
            <td>
                <a href="/questions/${q.id}/edit">Editar</a>
                <a href="/questions/${q.id}/answers">Respuestas</a>
                <button onclick="del(${q.id})">Borrar</button>
            </td>
        </tr>`).join('');
}
async function del(id) {
    if (!confirm('¿Borrar?')) return;
    await api.del('/questions/' + id);
    loadQs();
}
document.getElementById('filtros').addEventListener('submit', e => { e.preventDefault(); loadQs(); });
(async () => { await loadCats(); await loadQs(); })();
</script>
@endpush
```

Aplica el mismo patrón a `answers/index.blade.php`.

---

## 8. Rutas Ludiweb

Descomenta en [LudiWeb/routes/web.php](LudiWeb/routes/web.php) las líneas marcadas como **Marta**.

---

## 9. Pruebas

- Colección **Postman** `BankApi/postman/Marta.json` con CRUD de `questions` y `answers` + casos de error de la regla 3/1 (intentar crear 4ª respuesta, intentar marcar 2ª correcta).
- Probar a mano cada formulario en navegador.

---

## Checklist de entrega

- [ ] Modelos `Question` y `Answer` con relaciones.
- [ ] FormRequests `Store/Update` para Question y Answer.
- [ ] Validación 3/1 en `AnswerController@store` y `@update`.
- [ ] `QuestionResource` y `AnswerResource`.
- [ ] `AnswerPublicResource` acordado con Mar (sin `es_correcta`).
- [ ] Filtros `?category_id=` y `?dificultad=` en `/questions`.
- [ ] Rutas activas en `routes/api.php`.
- [ ] Vistas Ludiweb: questions index/create/edit y answers index.
- [ ] Colección Postman con casos OK y casos de error.

---

## Bloqueos / dependencias

- **Necesitas**: el modelo `Category` y la tabla con datos de prueba (lo hace **Nico**). Hasta que mergee, puedes insertar categorías a mano por SQL.
- **Te bloquea**: Nico (Categorías).
- **Bloqueas a**: Mar (necesita tus modelos y endpoints para construir partidas).
