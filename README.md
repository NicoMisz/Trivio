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

- [NICO.md](NICO.md) — **Persona 2**: Categorías + Autenticación (empieza primero, desbloquea a los demás)
- [MARTA.md](MARTA.md) — **Persona 1**: Preguntas + Respuestas
- [MAR.md](MAR.md) — **Persona 3**: Partidas + Puntuación

## Estructura del dominio

| Tabla | Dueño | Notas |
|---|---|---|
| `categories` | Nico | nombre, descripcion |
| `users` | Nico (auth) | extendido con Sanctum |
| `questions` | Marta | enunciado, dificultad (easy/medium/hard), imagen, FK category |
| `answers` | Marta | texto, es_correcta, FK question. 3 por pregunta, 1 correcta |
| `games` | Mar | user_id, puntuacion, started_at, finished_at |
| `game_questions` | Mar | game_id, question_id, answer_id, is_correct |

## Endpoints planificados

Definidos como placeholders en [BankApi/routes/api.php](BankApi/routes/api.php). Todos detrás de `auth:sanctum` excepto `/register` y `/login`.

| Método | Ruta | Dueño |
|---|---|---|
| POST | `/api/register` | Nico |
| POST | `/api/login` | Nico |
| POST | `/api/logout` | Nico |
| GET | `/api/me` | (base) |
| `apiResource` | `/api/categories` | Nico |
| `apiResource` | `/api/questions` | Marta |
| `apiResource` | `/api/questions/{question}/answers` | Marta |
| GET / POST | `/api/games`, `/api/games/{id}` | Mar |
| GET | `/api/games/{id}/questions` | Mar |
| POST | `/api/games/{id}/answers` | Mar |
| POST | `/api/games/{id}/score` | Mar |
