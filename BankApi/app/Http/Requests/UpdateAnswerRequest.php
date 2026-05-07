<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnswerRequest extends FormRequest
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
     * Regles de validació que s'han de complir per a actualitzar una resposta.
     * En aquest cas, els camps són 'sometimes' ja que és validen si l'usuari 
     * proporciona el camp.
     */
    public function rules(): array
    {
        return [
            'texto' => ['sometimes', 'string'],
            'es_correcta' => ['sometimes', 'boolean'],
        ];
    }
}
