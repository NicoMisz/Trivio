# Nico — Categorías y Autenticación (Persona 2)

Te encargas del **Punto 1** del enunciado (CRUD de Categorías) y de toda la **autenticación con Sanctum**, base que usarán Marta y Mar. Eres el primero en arrancar — desbloqueas a los demás.

> Sanctum ya está instalado, la migración de `personal_access_tokens` ejecutada y `HasApiTokens` añadido al modelo `User`. Solo te queda implementar el flujo. La migración de `categories` también está creada y ejecutada.

---

## 1. BankApi — Modelo Category

```bash
php artisan make:model Category
```

```php
protected $fillable = ['nombre', 'descripcion'];
public function questions() { return $this->hasMany(Question::class); }
```

> El modelo `Question` lo crea Marta. La relación `questions()` puede quedarse referenciando una clase que aún no existe — Eloquent no falla hasta que la usas.

---

## 2. BankApi — FormRequests + Resource + Controller

```bash
php artisan make:request StoreCategoryRequest
php artisan make:request UpdateCategoryRequest
php artisan make:resource CategoryResource
php artisan make:controller Api/CategoryController --api --model=Category
```

### `StoreCategoryRequest`
```php
public function authorize(): bool { return true; }
public function rules(): array {
    return [
        'nombre'      => ['required', 'string', 'max:100'],
        'descripcion' => ['nullable', 'string'],
    ];
}
```

`UpdateCategoryRequest` igual con `sometimes` donde proceda.

### `CategoryResource`
```php
return [
    'id' => $this->id,
    'nombre' => $this->nombre,
    'descripcion' => $this->descripcion,
];
```

### `Api/CategoryController`

Implementa los 5 métodos `index, store, show, update, destroy` usando `StoreCategoryRequest`, `UpdateCategoryRequest` y `CategoryResource`.

---

## 3. BankApi — AuthController

```bash
php artisan make:controller Api/AuthController
```

```php
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $r) {
        $data = $r->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $user  = User::create($data);
        $token = $user->createToken('ludiweb')->plainTextToken;
        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $r) {
        $data = $r->validate([
            'email'    => ['required', 'email'],
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
}
```

---

## 4. BankApi — Rutas

En [BankApi/routes/api.php](BankApi/routes/api.php), descomenta tus líneas (marcadas como **Nico**):

Públicas:
```php
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
```

Dentro del grupo `auth:sanctum`:
```php
Route::post('/logout', [AuthController::class, 'logout']);
Route::apiResource('categories', CategoryController::class);
```

Añade los `use` arriba.

---

## 5. Ludiweb — Vistas

Crea en `LudiWeb/resources/views/`:

### Categorías
- `categories/index.blade.php` — tabla + botón "Nueva".
- `categories/create.blade.php` — form con `nombre` y `descripcion`.
- `categories/edit.blade.php` — igual pero precarga datos.

### Auth
- `auth/login.blade.php`
- `auth/register.blade.php`
- `auth/logout.blade.php`

### Ejemplo `auth/login.blade.php`
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
    } catch (err) {
        document.getElementById('err').textContent = err.message;
    }
});
</script>
@endpush
```

### `auth/logout.blade.php`
```blade
@extends('layouts.app')
@section('content')<p>Cerrando sesión…</p>@endsection
@push('scripts')
<script>
(async () => {
    try { await api.post('/logout'); } catch (e) {}
    api.auth.clearToken();
    api.auth.clearUser();
    location.href = '/';
})();
</script>
@endpush
```

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
        document.getElementById('rows').innerHTML = (data.data || data).map(c => `
            <tr>
                <td>${c.id}</td>
                <td>${c.nombre}</td>
                <td>${c.descripcion || ''}</td>
                <td>
                    <a href="/categories/${c.id}/edit">Editar</a>
                    <button onclick="del(${c.id})">Borrar</button>
                </td>
            </tr>`).join('');
    } catch (e) {
        document.getElementById('rows').innerHTML = `<tr><td colspan="4" class="error">${e.message}</td></tr>`;
    }
})();
async function del(id) {
    if (!confirm('¿Borrar?')) return;
    await api.del('/categories/' + id);
    location.reload();
}
</script>
@endpush
```

---

## 6. Rutas Ludiweb

Descomenta en [LudiWeb/routes/web.php](LudiWeb/routes/web.php) las líneas marcadas como **Nico**.

---

## 7. Pruebas

Colección **Postman** `BankApi/postman/Nico.json`:
- `POST /api/register`, `POST /api/login`, `GET /api/me`, `POST /api/logout`.
- CRUD completo de categorías.
- Casos de error: login con contraseña mala (422), email duplicado en registro (422), categoría sin token (401).

En navegador: registrar → login → cabecera con tu nombre → crear/editar/borrar categoría → logout → la cabecera vuelve al estado anónimo.

> Pista: en Postman crea una variable `{{token}}` y úsala en el header `Authorization: Bearer {{token}}`. Configúrala automáticamente en el "Tests" del request de login con `pm.collectionVariables.set('token', pm.response.json().token)`.

---

## Checklist de entrega

- [ ] Modelo `Category` con `fillable` y relación `questions()`.
- [ ] FormRequests `Store/UpdateCategoryRequest`.
- [ ] `CategoryResource`.
- [ ] `Api/CategoryController` con CRUD completo.
- [ ] `Api/AuthController` con register/login/logout.
- [ ] Rutas activas en `routes/api.php` (auth y categorías).
- [ ] Vistas Ludiweb: login, register, logout, categories index/create/edit.
- [ ] Cabecera reactiva (logueado vs anónimo) — la base ya está en `auth-ui.js`, comprueba que funciona.
- [ ] Colección Postman con casos OK y casos de error.

---

## Bloqueos / dependencias

- **Te bloquea**: nadie. Eres el primero en arrancar.
- **Bloqueas a**:
  - Marta (necesita `Category` para sus FK y para que su select de categorías cargue datos).
  - Mar (necesita auth para probar partidas con un usuario real).
- **Estrategia**: prioriza el backend (modelo, controller, auth, rutas) y mergea cuanto antes a `main`. Las vistas de Ludiweb pueden venir después sin bloquear a nadie.
