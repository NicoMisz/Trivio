<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    //

    protected $fillable = [
        'question_id',
        'texto',
        'es_correcta',
    ];

    protected $casts = [
        'es_correcta' => 'boolean'
    ];

    public function question() { 
        return $this->belongsTo(Question::class); 
    }
}
