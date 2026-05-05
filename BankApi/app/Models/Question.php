<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    //

    protected $fillable = [
        'category_id',
        'enunciado',
        'dificultad',
        'imagen',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function answers() {
        return $this->hasMany(Answer::class);
    }
    
}
