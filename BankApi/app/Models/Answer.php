<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    // Taula associada al model
    protected $fillable = [
        'question_id',
        'texto',
        'es_correcta',
    ];

    // Convertir el camp 'es_correcta' a boolean quan es recupera de la BBDD
    protected $casts = [
        'es_correcta' => 'boolean'
    ];

    // Propietat que indica que el model ha d'utilitzar timestamps (created_at i updated_at)
    public $timestamps = true;

    // Relació: una resposta pertany a una pregunta i una pregunta pot tenir moltes respostes.
    public function question() { 
        return $this->belongsTo(Question::class); 
    }
}
