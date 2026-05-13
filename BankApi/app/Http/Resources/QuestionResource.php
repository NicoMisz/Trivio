<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transforma les preguntes del model en un Json estructurat
     */
    public function toArray(Request $request): array
    {
        return [ 
            'id' => $this-> id,
            'category_id' => $this -> category_id,
            'enunciado' => $this->enunciado,
            'dificultad' => $this->dificultad,
            'imagen' => $this->imagen,

            //Carregar la categoria i respostes si estan disponibles
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),

            //Afegir les dates de creació i actualització
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
