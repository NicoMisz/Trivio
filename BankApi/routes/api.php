<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
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
// Nico — Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ----- Rutas autenticadas (Sanctum) -----
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', fn (Request $r) => $r->user());

    // Nico — Auth + Categorías
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('categories', CategoryController::class);

    // Marta — Preguntas + Respuestas
    // Route::apiResource('questions',         QuestionController::class);
    // Route::apiResource('questions.answers', AnswerController::class)->shallow();

    // Mar — Partidas
    // Route::apiResource('games', GameController::class)->only(['index', 'store', 'show']);
    // Route::get ('games/{game}/questions', [GameController::class, 'questions']);
    // Route::post('games/{game}/answers',   [GameController::class, 'submitAnswers']);
    // Route::post('games/{game}/score',     [GameController::class, 'score']);
});
