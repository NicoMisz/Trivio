<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnswerRequest extends FormRequest
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
     * Regles de validació que s'han de complir per a crear les respostes
     */
    public function rules(): array
    {
        return [
            'texto' => ['required', 'string'], // Text obligatori
            'es_correcta' => ['required', 'boolean'], // ture o false
        ];
    }
}
