<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API de BankApi
|--------------------------------------------------------------------------
| Estructura acordada para los 3 desarrolladores. Cada uno descomenta y
| completa la sección que le corresponde.
*/

// ----- Rutas públicas -----
// Persona 2 — Auth
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login',    [AuthController::class, 'login']);

// ----- Rutas autenticadas (Sanctum) -----
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', fn (Request $r) => $r->user());

    // Persona 2 — Auth
    // Route::post('/logout', [AuthController::class, 'logout']);

    // Persona 1 — Categorías y Preguntas
    // Route::apiResource('categories', CategoryController::class);
    // Route::apiResource('questions',  QuestionController::class);

    // Persona 2 — Respuestas (anidadas bajo question)
    // Route::apiResource('questions.answers', AnswerController::class)->shallow();

    // Persona 3 — Partidas
    // Route::apiResource('games', GameController::class)->only(['index', 'store', 'show']);
    // Route::get ('games/{game}/questions', [GameController::class, 'questions']);
    // Route::post('games/{game}/answers',   [GameController::class, 'submitAnswers']);
    // Route::post('games/{game}/score',     [GameController::class, 'score']);
});
