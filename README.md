# Trivio

Proyecto AEA04 M7 DAW.

Dos aplicaciones Laravel:

- **BankApi** — API REST de banco de preguntas (categorías, preguntas, respuestas, partidas).
- **Ludiweb** — cliente web que consume BankApi mediante `fetch` desde JavaScript.

## Requisitos

- PHP 8.4+ (las dependencias de Laravel 13 lo exigen). Comprueba con `php -v`.
- Composer.
- Navegador moderno (la web usa `fetch` y `localStorage`).

## Estado del proyecto

Rama activa: `develop`.

| Punto del enunciado | Estado | Dueño |
|---|---|---|
| 1. CRUD Categorías | ✅ Backend + Ludiweb | Nico |
| 2. CRUD Preguntas | ✅ Backend · ⬜ Ludiweb | Marta |
| 3. CRUD Respuestas (3/1) | ✅ Backend · ⬜ Ludiweb | Marta |
| 4. Partidas (crear, preguntas, puntuar) | ⬜ Por hacer | Mar |
| 5. Partidas por usuario (auth, guardar) | ⬜ Por hacer | Mar |
| Autenticación Sanctum | ✅ Backend + Ludiweb | Nico |

Postman:
- ✅ `BankApi/postman/Nico.json` — auth + categorías (con tests automáticos).
- ⬜ Falta Marta y Mar.

Reparto detallado en [NICO.md](NICO.md), [MARTA.md](MARTA.md), [MAR.md](MAR.md).

## Arranque

Necesitas dos terminales: una por cada app.

### Terminal 1 — BankApi (puerto 8000)

```bash
cd BankApi
composer install               # solo la primera vez
cp .env.example .env           # si no existe
php artisan key:generate       # solo la primera vez
php artisan migrate            # crea las tablas
php artisan serve --port=8000
```

API base: `http://localhost:8000/api`. Health check: `http://localhost:8000/up`.

### Terminal 2 — Ludiweb (puerto 8001)

```bash
cd LudiWeb
composer install               # solo la primera vez
cp .env.example .env           # si no existe
php artisan key:generate       # solo la primera vez
php artisan serve --port=8001
```

Web: `http://localhost:8001`.

La URL de BankApi que usa Ludiweb se configura en `LudiWeb/.env` con `BANKAPI_URL` (por defecto `http://localhost:8000`).

## Estructura del dominio

| Tabla | Dueño | Columnas principales |
|---|---|---|
| `categories` | Nico | nombre, descripcion |
| `users` | (base) + Nico (Sanctum) | name, email, password |
| `personal_access_tokens` | (Sanctum) | tokens emitidos por login |
| `questions` | Marta | enunciado, dificultad (easy/medium/hard), imagen, FK category |
| `answers` | Marta | texto, es_correcta, FK question. **3 por pregunta, 1 correcta** |
| `games` | Mar | user_id, puntuacion, started_at, finished_at |
| `game_questions` | Mar | game_id, question_id, answer_id, is_correct |

## Endpoints

Todos detrás de `auth:sanctum` excepto `/register` y `/login`.

### Auth (Nico)

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/register` | crea usuario y devuelve token |
| POST | `/api/login` | devuelve token |
| POST | `/api/logout` | revoca el token actual |
| GET | `/api/me` | datos del usuario logueado |

### Categorías (Nico)

| Método | Ruta |
|---|---|
| GET | `/api/categories` |
| POST | `/api/categories` |
| GET | `/api/categories/{id}` |
| PUT/PATCH | `/api/categories/{id}` |
| DELETE | `/api/categories/{id}` |

### Preguntas (Marta)

| Método | Ruta | Notas |
|---|---|---|
| GET | `/api/questions` | filtros `?category_id=` y `?dificultad=` |
| POST | `/api/questions` | |
| GET | `/api/questions/{id}` | |
| PUT/PATCH | `/api/questions/{id}` | |
| DELETE | `/api/questions/{id}` | |

### Respuestas (Marta · rutas anidadas con `shallow()`)

| Método | Ruta | Notas |
|---|---|---|
| GET | `/api/questions/{question}/answers` | |
| POST | `/api/questions/{question}/answers` | máx 3, máx 1 correcta |
| GET | `/api/answers/{id}` | |
| PUT/PATCH | `/api/answers/{id}` | |
| DELETE | `/api/answers/{id}` | |

### Partidas (Mar · pendiente)

| Método | Ruta |
|---|---|
| GET | `/api/games` |
| POST | `/api/games` |
| GET | `/api/games/{id}` |
| GET | `/api/games/{id}/questions` |
| POST | `/api/games/{id}/answers` |
| POST | `/api/games/{id}/score` |

## Pruebas con Postman

Cada persona tiene su colección en `BankApi/postman/<Nombre>.json`. Importa el JSON en Postman y ejecuta los requests en orden — el login guarda automáticamente el token en la variable `{{token}}`.

Para correr todos los tests de una colección de un golpe: clic derecho en la colección → **Run collection**.

Casos cubiertos:

- ✅ Camino feliz: register → login → me → operación CRUD → logout.
- ✅ Casos de error: 401 sin token, 422 validación, 404 not found, 422 credenciales inválidas, 422 email duplicado.

## Flujo de la app (cómo encaja todo)

1. Usuario entra en `http://localhost:8001/register` → Ludiweb sirve un formulario HTML.
2. Submit → JavaScript en la página hace `fetch('http://localhost:8000/api/register', ...)`.
3. BankApi crea el `User`, emite un token Sanctum, lo devuelve en JSON.
4. JavaScript guarda el token en `localStorage` y redirige a `/`.
5. A partir de aquí, cada llamada a la API añade `Authorization: Bearer <token>` (lo hace `public/js/apiClient.js` automáticamente).
6. El middleware `auth:sanctum` valida el token mirando la tabla `personal_access_tokens`.
7. Logout → BankApi borra ese token → siguiente request devuelve 401.

CORS está configurado en `BankApi/config/cors.php` para permitir `http://localhost:8001` y `http://127.0.0.1:8001`.

## Comandos útiles

```bash
# Ver el estado de las migraciones
cd BankApi && php artisan migrate:status

# Ver todas las rutas registradas
cd BankApi && php artisan route:list --path=api

# Reset total de la BD (¡borra datos!)
cd BankApi && php artisan migrate:fresh

# Si tocaste .env y no se aplica el cambio
cd BankApi && php artisan config:clear

# Matar servidores que se quedaron colgados
pkill -f "artisan serve"
```

## Mejoras pendientes (opcionales del enunciado)

- Ranking global de jugadores.
- Preguntas con imágenes (la columna `imagen` ya existe).
- Temporizador por pregunta.
- Estadísticas de juego.
