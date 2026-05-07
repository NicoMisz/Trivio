<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Retornar 'true' perquè qualsevol usuari autenticat pot crear preguntes
     * (canvair a 'false' en cas que no volguem que sigui així)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regles de validació que s'han de complir per a crear una pregunta
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'], // La categoria ha d'existir
            'enunciado' => ['required', 'string'], //Text de la pregunta obligatori
            'dificultad' => ['required', 'in:easy,medium,hard'], // Dificultat amb 3 valors possibles
            'imagen' => ['nullable', 'string', 'max:255'], // Opcional, url o ruta de la imatge associada a la pregunta
        ];
    }
}
