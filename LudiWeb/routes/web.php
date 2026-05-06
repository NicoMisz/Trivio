<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas web Ludiweb
|--------------------------------------------------------------------------
| Cada persona descomenta y completa la sección que le corresponde.
| Las vistas hacen fetch() a BankApi mediante public/js/apiClient.js.
*/

Route::view('/', 'home');

// Nico — Auth + Categorías
Route::view('/login',                'auth.login');
Route::view('/register',             'auth.register');
Route::view('/logout',               'auth.logout');
Route::view('/categories',           'categories.index');
Route::view('/categories/create',    'categories.create');
Route::view('/categories/{id}/edit', 'categories.edit');

// Marta — Preguntas + Respuestas
// Route::view('/questions',              'questions.index');
// Route::view('/questions/create',       'questions.create');
// Route::view('/questions/{id}/edit',    'questions.edit');
// Route::view('/questions/{id}/answers', 'answers.index');

// Mar — Partidas
// Route::view('/games',      'games.index');
// Route::view('/games/play', 'games.play');
// Route::view('/games/{id}', 'games.show');
