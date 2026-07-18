<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Homework extends Model
{
    protected $table = 'homeworks';
    
    protected $guarded = [];

    protected static function booted()
    {
        static::created(function ($homework) {
            \App\Services\TaskTrackingService::track(
                $homework->teacher_id ?? auth()->id(),
                $homework->subject_id,
                'homework_sent'
            );
        });
    }

    protected $casts = [
        'due_date' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Get the subject that owns the homework.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    /**
     * Get the teacher that owns the homework.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }
}
