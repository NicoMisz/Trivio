<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutes per als formularis i taules de preguntes i respostes que seran usades 
// per l'equip de frontend per gestionar el contingut de la base de dades.

Route::get('/questions', function () { return view('questions.index'); });
Route::get('/questions/create', function () { return view ('questions.create'); });
Route::get('/questions/{id}/edit', function () { return view('questions.edit'); });
Route::get('/questions/{id}/answers', function () { return view('answers.index');});
