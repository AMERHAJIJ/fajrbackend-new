<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $guarded = [];

    /**
     * Get the video that owns the quiz.
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }

    /**
     * Get the questions for the quiz.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'quiz_id', 'id');
    }
}