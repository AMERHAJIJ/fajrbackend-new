<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherTaskTracker extends Model
{
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'attendance_taken' => 'boolean',
        'recitation_recorded' => 'boolean',
        'next_recitation_set' => 'boolean',
        'whatsapp_sent' => 'boolean',
        'homework_sent' => 'boolean',
    ];

    /**
     * Get the teacher that owns the task tracker.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }

    /**
     * Get the subject that the tasks are related to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    /**
     * Get completion percentage for the day
     */
    public function getCompletionPercentageAttribute(): int
    {
        $tasks = [
            $this->attendance_taken,
            $this->recitation_recorded,
            $this->next_recitation_set,
            $this->whatsapp_sent,
            $this->homework_sent,
        ];

        $completed = array_filter($tasks);
        return round((count($completed) / count($tasks)) * 100);
    }

    /**
     * Get status color based on completion percentage
     */
    public function getStatusColorAttribute(): string
    {
        $percentage = $this->completion_percentage;
        
        if ($percentage >= 80) return 'success';
        if ($percentage >= 60) return 'warning';
        return 'danger';
    }

    /**
     * Get status text based on completion percentage
     */
    public function getStatusTextAttribute(): string
    {
        $percentage = $this->completion_percentage;
        
        if ($percentage >= 80) return 'ممتاز';
        if ($percentage >= 60) return 'جيد';
        if ($percentage >= 40) return 'متوسط';
        return 'ضعيف';
    }
}
