<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    /**
     * Transforma les respostes del model en un Json estructurat
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this -> id,
            'question_id' => $this -> question_id,
            'text' => $this -> texto,
            'es_correcta' => $this -> es_correcta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
