<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
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
     * Regles de validació que s'han de complir per a actualitzar una pregunta
     * En aquest cas, els camps són 'sometimes' ja que és validen si l'usuari 
     * proporciona el camp.
     */
    public function rules(): array
    {
        return [
            //
            'category_id' => ['sometimes', 'exists:categories,id'],
            'enunciado' => ['sometimes', 'string'],
            'dificultad' => ['sometimes', 'in:easy,medium,hard'],
            'imagen' => ['nullable', 'string', 'max:255'],
        ];
    }
}
