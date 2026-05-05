# Mar — Partidas y Puntuación (Persona 3)

Te encargas de los **Puntos 4 y 5** del enunciado: gestión de partidas (crear, obtener preguntas, puntuar) y la versión por usuario autenticado (guardar respuestas, guardar puntuación, historial). Es la parte más pesada y depende de Marta y Nico.

> Las migraciones `games` y `game_questions` ya están creadas y ejecutadas. Lectura recomendada antes de empezar:
> - [BankApi/database/migrations/2026_04_29_163704_create_games_table.php](BankApi/database/migrations/2026_04_29_163704_create_games_table.php)
> - [BankApi/database/migrations/2026_04_29_163705_create_game_questions_table.php](BankApi/database/migrations/2026_04_29_163705_create_game_questions_table.php)

---

## 1. BankApi — Modelos

### `Game.php`

```php
protected $fillable = ['user_id', 'puntuacion', 'started_at', 'finished_at'];
protected $casts = [
    'started_at' => 'datetime',
    'finished_at' => 'datetime',
];
public function user() { return $this->belongsTo(User::class); }
public function questions() {
    return $this->belongsToMany(Question::class, 'game_questions')
                ->withPivot(['answer_id', 'is_correct', 'answered_at'])
                ->withTimestamps();
}
public function gameQuestions() { return $this->hasMany(GameQuestion::class); }
```

### `GameQuestion.php`

```php
protected $fillable = ['game_id', 'question_id', 'answer_id', 'is_correct', 'answered_at'];
protected $casts = [
    'is_correct' => 'boolean',
    'answered_at' => 'datetime',
];
public function game()     { return $this->belongsTo(Game::class); }
public function question() { return $this->belongsTo(Question::class); }
public function answer()   { return $this->belongsTo(Answer::class); }
```

---

## 2. BankApi — Constantes y puntuación

Crea `app/Support/Scoring.php` (o pon constantes en `Game`):

```php
public const QUESTIONS_PER_GAME = 10;
public const POINTS = ['easy' => 1, 'medium' => 2, 'hard' => 3];
```

Función para calcular la puntuación de una partida:

```php
$total = $game->gameQuestions()
    ->where('is_correct', true)
    ->with('question')
    ->get()
    ->sum(fn ($gq) => self::POINTS[$gq->question->dificultad] ?? 0);
```

---

## 3. BankApi — Controlador `Api/GameController`

```bash
php artisan make:controller Api/GameController --api --model=Game
```

Endpoints (todos detrás de `auth:sanctum`):

### `POST /api/games` — crear partida
1. Crea el `Game` con `user_id = auth()->id()`, `started_at = now()`.
2. Selecciona `Game::QUESTIONS_PER_GAME` preguntas aleatorias: `Question::inRandomOrder()->limit(N)->pluck('id')`.
3. Inserta una fila en `game_questions` por cada pregunta.
4. Devuelve el `Game` con sus preguntas (sin marcar la correcta).

### `GET /api/games/{game}/questions` — obtener preguntas
- Verifica que `$game->user_id === auth()->id()` (si no, 403).
- Devuelve preguntas con sus 3 respuestas, **sin el campo `es_correcta`**.
- Coordina con Marta para usar un Resource específico (p. ej. `AnswerPublicResource`) o un campo extra.

### `POST /api/games/{game}/answers` — guardar respuestas
Body esperado:
```json
{ "answers": [ { "question_id": 5, "answer_id": 14 }, ... ] }
```

Para cada pareja:
- Comprueba que la pregunta pertenece a la partida.
- Carga la `Answer` y mira `es_correcta`.
- Actualiza la fila correspondiente en `game_questions`: `answer_id`, `is_correct`, `answered_at = now()`.

### `POST /api/games/{game}/score` — calcular y guardar puntuación
- Calcula puntuación ponderada (ver §2).
- Guarda en `games.puntuacion` y `finished_at = now()`.
- Devuelve `{ puntuacion, total_correctas, total_preguntas }`.

### `GET /api/games` — historial del usuario
- Devuelve solo las partidas del usuario logueado, ordenadas por `started_at` desc.

### `GET /api/games/{game}` — detalle
- Para mostrar revisión: cada pregunta con la respuesta elegida y la correcta.

---

## 4. BankApi — Resource

`GameResource` y `GameQuestionResource` para serializar.

```php
// GameResource
return [
    'id' => $this->id,
    'puntuacion' => $this->puntuacion,
    'started_at' => $this->started_at,
    'finished_at' => $this->finished_at,
    'questions' => GameQuestionResource::collection($this->whenLoaded('gameQuestions')),
];
```

---

## 5. BankApi — Rutas

Descomenta en [BankApi/routes/api.php](BankApi/routes/api.php) las líneas marcadas como **Mar**:

```php
Route::apiResource('games', GameController::class)->only(['index', 'store', 'show']);
Route::get ('games/{game}/questions', [GameController::class, 'questions']);
Route::post('games/{game}/answers',   [GameController::class, 'submitAnswers']);
Route::post('games/{game}/score',     [GameController::class, 'score']);
```

---

## 6. Ludiweb — Vistas

### `games/index.blade.php` — historial + botón "Jugar"

Llama a `api.get('/games')` y muestra tabla. Botón "Nueva partida" lleva a `/games/play`.

### `games/play.blade.php` — flujo completo de partida

Pseudocódigo:

```js
const game = await api.post('/games');
const data = await api.get(`/games/${game.id}/questions`);
// data.questions = [{id, enunciado, dificultad, answers: [{id, texto}, ...]}, ...]
// Mostrar todas las preguntas en un formulario, recoger answer_id por question_id
// Al enviar:
await api.post(`/games/${game.id}/answers`, { answers: [...] });
const result = await api.post(`/games/${game.id}/score`);
// Redirige a /games/{id} para revisión
location.href = `/games/${game.id}`;
```

### `games/show.blade.php` — revisión de partida

Carga `/games/{id}`, muestra cada pregunta con la respuesta elegida marcada y la correcta resaltada.

---

## 7. Rutas Ludiweb

Descomenta en [LudiWeb/routes/web.php](LudiWeb/routes/web.php) las líneas marcadas como **Mar**.

---

## 8. Pruebas

- Colección **Postman** `BankApi/postman/Mar.json` con el flujo completo: login → crear partida → obtener preguntas → enviar respuestas → calcular puntuación → ver historial.
- Probar en navegador la partida completa.
- Caso límite: enviar `answer_id` de una pregunta que no es de tu partida → debe rechazar.

---

## Checklist de entrega

- [ ] Modelos `Game` y `GameQuestion` con relaciones.
- [ ] Constantes `QUESTIONS_PER_GAME` y `POINTS`.
- [ ] `Api/GameController` con los 6 endpoints.
- [ ] Verificación `user_id === auth()->id()` en cada endpoint con `{game}`.
- [ ] `es_correcta` oculto al devolver preguntas durante una partida.
- [ ] Puntuación ponderada por dificultad.
- [ ] `GameResource` y `GameQuestionResource`.
- [ ] Rutas activas en `routes/api.php`.
- [ ] Vistas Ludiweb: index, play, show.
- [ ] Colección Postman con flujo completo.

---

## Bloqueos / dependencias

- **Necesitas**: modelo `Category` y auth (Nico) + modelos `Question` y `Answer` (Marta).
- **Te bloquean**: Nico y Marta.
- **Estrategia**: empieza por modelos, controlador y rutas con datos mock (puedes crear preguntas a mano por SQL en `database.sqlite`). Cuando Nico y Marta mergeen, integra de verdad.
- **Bloqueas a**: nadie.

---

## Nota sobre `es_correcta` en partidas

Cuando devuelves preguntas dentro de un `GET /games/{id}/questions`, **no debe filtrarse cuál es la respuesta correcta**. Opciones:

1. Crear `AnswerPublicResource` (sin el campo) y usarlo en este endpoint.
2. Pasar un flag al `AnswerResource` y omitir el campo cuando se está jugando.

Pacta con Marta cuál usáis (es ella la dueña del `AnswerResource`).
