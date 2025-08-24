<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo ;

class Answer extends Model
{
    protected $guarded = [];

    public function question(): BelongsTo 
    {
        return $this->BelongsTo(Question::class, 'question_id', 'id');   
    }
}