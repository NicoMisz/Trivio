<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);

        return [ 
            'id' => $this-> id,
            'category_id' => $this -> category_id,
            'enunciado' => $this->enunciado,
            'dificultad' => $this->dificultad,
            'imagen' => $this->imagen,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}
