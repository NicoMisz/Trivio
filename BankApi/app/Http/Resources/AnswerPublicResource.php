<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerPublicResource extends JsonResource
{
    /**
     * Resource que transforma les respostes del model en un Json estructurat. 
     * Usarem aquest per partides públiques, per aquest motiu no s'inclou el 
     * camp 'es_correcta' ja que el jugador no ha de poder veure quina es la 
     * resposta correcta.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this -> id,
            'question_id' => $this -> question_id,
            'text' => $this -> texto,
        ];
    }
}
