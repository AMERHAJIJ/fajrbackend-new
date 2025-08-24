<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAnswer extends Model
{
    protected $guarded = [];

    /**
     * Get the student that owns the student answer record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    /**
     * Get the answer that owns the student answer record.
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'answer_id', 'id');
    }

    /**
     * Get the question that owns the student answer record.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }

    /**
     * Get the quiz that owns the student answer record.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }
}