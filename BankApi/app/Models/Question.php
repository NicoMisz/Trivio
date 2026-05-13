<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    // Taula associada al model
    protected $fillable = [
        'category_id',
        'enunciado',
        'dificultad',
        'imagen',
    ];

    // Propietat que indica que el model ha d'utilitzar timestamps (created_at i updated_at)
    public $timestamps = true;

    // Relació: una pregunta pertany a una categoria i una categoria pot tenir moltes preguntes.
     public function category() {
        return $this->belongsTo(Category::class);
    }

    // Relació: Una pregunta pot tenir moltes respostes i una resposta pertany a una pregunta.
    public function answers() {
        return $this->hasMany(Answer::class);
    }
    
}
