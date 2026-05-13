<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * GET /api/questions
     * Llistar preguntes amb filtres opcionals per categoria i dificultat
     */
    public function index(Request $request)
    {
        $query = Question::query();

        // Filtrar per categoria si s'especifica
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtrar per dificultat si s'especifica
        if ($request->filled('dificultad')) {
            $query->where('dificultad', $request->dificultad);
        }

        // Carregar relacions per evitar N+1 queries
        $questions = $query->with('category', 'answers')->get();

        return QuestionResource::collection($questions);
    }

    /**
     * POST /api/questions
     * Crear una nova pregunta
     */
    public function store(StoreQuestionRequest $request)
    {
        $question = Question::create($request->validated());
        return new QuestionResource($question->load('category', 'answers'));
    }

    /**
     * GET /api/questions/{id}
     * Obtenir una pregunta específica
     */
    public function show(Question $question)
    {
        return new QuestionResource($question->load('category', 'answers'));
    }

    /**
     * PUT/PATCH /api/questions/{id}
     * Actualitzar una pregunta
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $question->update($request->validated());
        return new QuestionResource($question->load('category', 'answers'));
    }

    /**
     * DELETE /api/questions/{id}
     * Eliminar una pregunta
     */
    public function destroy(Question $question)
    {
        $question->delete();
        return response()->noContent();
        //return response()->json(['message' => 'Pregunta eliminada correctamente.'], 200);
    }
}
