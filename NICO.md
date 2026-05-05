# Persona 2 — Respuestas y Autenticación

Te encargas del **Punto 3** (Respuestas) y de toda la **autenticación con Sanctum**, base que usarán Persona 1 y Persona 3.

> Sanctum ya está instalado, la migración de `personal_access_tokens` ejecutada, y `HasApiTokens` añadido al modelo `User`. Solo te queda implementar el flujo.

---

## 1. BankApi — Autenticación

### Controlador `Api/AuthController`

```bash
php artisan make:controller Api/AuthController
```

Implementa:

```php
public function register(Request $r) {
    $data = $r->validate([
        'name' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'unique:users,email'],
        'password' => ['required', 'string', 'min:6'],
    ]);
    $data['password'] = Hash::make($data['password']);
    $user = User::create($data);
    $token = $user->createToken('ludiweb')->plainTextToken;
    return response()->json(['user' => $user, 'token' => $token], 201);
}

public function login(Request $r) {
    $data = $r->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);
    $user = User::where('email', $data['email'])->first();
    if (!$user || !Hash::check($data['password'], $user->password)) {
        return response()->json(['message' => 'Credenciales inválidas'], 422);
    }
    $token = $user->createToken('ludiweb')->plainTextToken;
    return response()->json(['user' => $user, 'token' => $token]);
}

public function logout(Request $r) {
    $r->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logout OK']);
}
```

### Rutas

Descomenta en [BankApi/routes/api.php](BankApi/routes/api.php):

```php
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
// y dentro del grupo auth:sanctum:
Route::post('/logout', [AuthController::class, 'logout']);
```

---

## 2. BankApi — Respuestas (CRUD)

### Modelo `Answer`

```bash
php artisan make:model Answer
```

```php
protected $fillable = ['question_id', 'texto', 'es_correcta'];
protected $casts = ['es_correcta' => 'boolean'];
public function question() { return $this->belongsTo(Question::class); }
```

> Coordínate con Persona 1 para que añada `public function answers() { return $this->hasMany(Answer::class); }` al modelo `Question`.

### Form Request `StoreAnswerRequest` / `UpdateAnswerRequest`

```php
public function rules(): array {
    return [
        'texto' => ['required', 'string'],
        'es_correcta' => ['required', 'boolean'],
    ];
}
```

### Validación adicional: 3 respuestas por pregunta, 1 correcta

Tu controlador `store` debe rechazar la creación si ya hay 3 respuestas, y rechazar `es_correcta=true` si ya existe una correcta.

```php
// dentro de store()
$question = Question::findOrFail($questionId);
if ($question->answers()->count() >= 3) {
    return response()->json(['message' => 'La pregunta ya tiene 3 respuestas.'], 422);
}
if ($request->es_correcta && $question->answers()->where('es_correcta', true)->exists()) {
    return response()->json(['message' => 'Ya existe una respuesta correcta.'], 422);
}
```

Lo mismo para `update` cuando se cambia `es_correcta` a true.

### Resource `AnswerResource`

```php
return [
    'id' => $this->id,
    'question_id' => $this->question_id,
    'texto' => $this->texto,
    'es_correcta' => $this->es_correcta,
];
```

> ⚠️ **Importante**: Persona 3 puede necesitar **ocultar `es_correcta`** cuando devuelve preguntas durante una partida. Hablad para acordar si se hace con un parámetro o con un Resource distinto.

### Controlador `Api/AnswerController`

```bash
php artisan make:controller Api/AnswerController --api --model=Answer
```

Acepta el `Question` por route binding (rutas anidadas).

### Rutas

Descomenta en [BankApi/routes/api.php](BankApi/routes/api.php):

```php
Route::apiResource('questions.answers', AnswerController::class)->shallow();
```

(`shallow()` hace que `show/update/destroy` no anclen al question — `/answers/{answer}`.)

---

## 3. Ludiweb — Login / Registro / Logout

### `auth/login.blade.php`

```blade
@extends('layouts.app')
@section('title', 'Login')
@section('content')
<h2>Iniciar sesión</h2>
<form id="f">
    <label>Email <input type="email" name="email" required></label>
    <label>Contraseña <input type="password" name="password" required></label>
    <button>Entrar</button>
    <p class="error" id="err"></p>
</form>
@endsection
@push('scripts')
<script>
document.getElementById('f').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    try {
        const data = await api.post('/login', { email: fd.get('email'), password: fd.get('password') });
        api.auth.setToken(data.token);
        api.auth.setUser(data.user);
        location.href = '/';
    } catch (err) { document.getElementById('err').textContent = err.message; }
});
</script>
@endpush
```

### `auth/register.blade.php`
Igual pero con `name`, `email`, `password` y POST a `/register`.

### `auth/logout.blade.php`
Llama a `api.post('/logout')`, limpia el token y redirige a `/`.

```blade
@extends('layouts.app')
@section('content')<p>Cerrando sesión…</p>@endsection
@push('scripts')
<script>
(async () => {
    try { await api.post('/logout'); } catch (e) {}
    api.auth.clearToken(); api.auth.clearUser();
    location.href = '/';
})();
</script>
@endpush
```

---

## 4. Ludiweb — Gestión de respuestas

`answers/index.blade.php`: dada una pregunta (id por URL), lista sus respuestas, permite añadir hasta 3, marcar/desmarcar la correcta y borrar.

Endpoint base: `/questions/{id}/answers` (GET y POST), `/answers/{id}` (PUT/DELETE).

---

## 5. Rutas Ludiweb

Descomenta las líneas marcadas como **Persona 2** en [LudiWeb/routes/web.php](LudiWeb/routes/web.php).

---

## 6. Pruebas

- Colección **Postman** `BankApi/postman/Persona2.json` con: register, login, logout, /me, CRUD de answers, casos de error (3 respuestas, 2 correctas).
- Verificar en navegador: registro → login → se ve el nombre en cabecera → logout.

---

## Checklist de entrega

- [ ] `AuthController` con register/login/logout.
- [ ] Modelo `Answer` con `fillable` y relación.
- [ ] FormRequests para Answer.
- [ ] Validación "3 respuestas / 1 correcta" en `store` y `update`.
- [ ] `AnswerResource`.
- [ ] `Api/AnswerController` con CRUD.
- [ ] Rutas activas en `routes/api.php` (auth y answers).
- [ ] Vistas Ludiweb: login, register, logout, gestión de answers.
- [ ] Cabecera muestra estado logueado/no logueado.
- [ ] Colección Postman.

---

## Bloqueos / dependencias

- **Necesitas**: el modelo `Question` y la relación `answers()` lista (Persona 1). Coordina al inicio.
- **Te bloquean**: Persona 1 (modelo Question debe existir antes de probar Answers de extremo a extremo).
- **Bloqueas a**: Persona 3 (necesita auth funcionando para probar partidas con usuario real, y necesita `AnswerResource` para devolver preguntas).
