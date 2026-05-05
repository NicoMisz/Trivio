# Trivio

Proyecto AEA04 M7 DAW.

Dos aplicaciones Laravel:

- **BankApi** — API REST de banco de preguntas (categorías, preguntas, respuestas, partidas).
- **Ludiweb** — cliente web que consume BankApi mediante `fetch` desde JavaScript.

## Requisitos

- PHP 8.4+ (las dependencias de Laravel 13 lo exigen). Comprueba con `php -v`.
- Composer.
- Node + npm (para Vite, opcional en Ludiweb).

## Arranque

### BankApi (puerto 8000)

```bash
cd BankApi
composer install
cp .env.example .env  # si no existe
php artisan key:generate
php artisan migrate
php artisan serve --port=8000
```

API base: `http://localhost:8000/api`

### Ludiweb (puerto 8001)

```bash
cd LudiWeb
composer install
cp .env.example .env  # si no existe
php artisan key:generate
php artisan serve --port=8001
```

Web base: `http://localhost:8001`

La URL de BankApi se configura en `LudiWeb/.env` con `BANKAPI_URL`.

## Reparto del trabajo

Cada persona tiene su parte detallada en:

- [PERSONA1.md](PERSONA1.md) — Categorías y Preguntas
- [PERSONA2.md](PERSONA2.md) — Respuestas y Autenticación
- [PERSONA3.md](PERSONA3.md) — Partidas y Puntuación

## Estructura del dominio

| Tabla | Pertenece a | Notas |
|---|---|---|
| `categories` | Persona 1 | nombre, descripcion |
| `questions` | Persona 1 | enunciado, dificultad (easy/medium/hard), imagen, FK category |
| `answers` | Persona 2 | texto, es_correcta, FK question. 3 por pregunta, 1 correcta |
| `users` | (base) | extendido con Sanctum (Persona 2) |
| `games` | Persona 3 | user_id, puntuacion, started_at, finished_at |
| `game_questions` | Persona 3 | game_id, question_id, answer_id, is_correct |

## Endpoints planificados

Definidos como placeholders en [BankApi/routes/api.php](BankApi/routes/api.php). Todos detrás de `auth:sanctum` excepto `/register` y `/login`.

| Método | Ruta | Persona |
|---|---|---|
| POST | `/api/register` | 2 |
| POST | `/api/login` | 2 |
| POST | `/api/logout` | 2 |
| GET | `/api/me` | 2 |
| `apiResource` | `/api/categories` | 1 |
| `apiResource` | `/api/questions` | 1 |
| `apiResource` | `/api/questions/{question}/answers` | 2 |
| GET / POST | `/api/games`, `/api/games/{id}` | 3 |
| GET | `/api/games/{id}/questions` | 3 |
| POST | `/api/games/{id}/answers` | 3 |
| POST | `/api/games/{id}/score` | 3 |
