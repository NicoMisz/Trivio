<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Http\Resources\AnswerResource;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    /**
     * GET /api/questions/{question}/answers
     * Llistar respostes d'una pregunta
     */
    public function index(Question $question)
    {
        return AnswerResource::collection($question->answers);
    }

    /**
     * POST /api/questions/{question}/answers
     * Crear una nova resposta
     * 
     * Validacions:
     * - Màxim 3 respostes
     * - Màxim 1 resposta correcta
     */
    public function store(StoreAnswerRequest $request, Question $question)
    {
        // Validar que no tinguem ja 3 respostes introduides
        if ($question->answers()->count() >= 3) {
            return response()->json(
                ['message' => 'La pregunta ya tiene 3 respuestas.'],
                422
            );
        }

        // Si marca una resposta com a correcte, validar que no n'hi hagi una altra ja
        if ($request->es_correcta && $question->answers()->where('es_correcta', true)->exists()) {
            return response()->json(
                ['message' => 'Ya existe una respuesta correcta.'],
                422
            );
        }

        // Crear la resposta si passa les validacions
        $answer = $question->answers()->create($request->validated());
        return new AnswerResource($answer);
    }

    /**
     * GET /api/answers/{answer}
     * Obtenir una resposta específica
     */
    public function show(Answer $answer)
    {
        return new AnswerResource($answer);
    }

    /**
     * PUT/PATCH /api/answers/{answer}
     * Actualitzar una resposta
     * 
     * Validació: si canviam a correcta, assegurar que no n'hi hagi una altra
     */
    public function update(UpdateAnswerRequest $request, Question $question, Answer $answer)
    {
        //Si canviem la resposta correcte, validar que no n'hi hagi ja una altra
        if ($request->es_correcta && $question->answers()
            ->where('id', '!=', $answer->id)
            ->where('es_correcta', true)
            ->exists()
        ) {
            return response()->json(
                ['message' => 'Ya existe otra respuesta correcta.'],
                422
            );
        }

        // Crear la resposta si passa les validacions
        $answer->update($request->validated());
        return new AnswerResource($answer);
    }

    /**
     * DELETE /api/answers/{answer}
     * Eliminar una resposta
     */
    public function destroy(Answer $answer)
    {
        $answer->delete();
        return response()->noContent();
        //return response()->json(['message' => 'Respuesta eliminada correctamente.'], 200);
    }
}
