<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'nombre'      => ['sometimes', 'required', 'string', 'max:100'],
            'descripcion' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
