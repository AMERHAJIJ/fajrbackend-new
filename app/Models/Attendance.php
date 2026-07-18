<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $guarded = [];

   
    protected static function booted()
    {
        static::created(function ($attendance) {
            \App\Services\TaskTrackingService::track(
                $attendance->teacher_id ?? auth()->id(),
                $attendance->subject_id,
                'attendance_taken'
            );
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    
    public function subject(): BelongsTo
    {
 
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }
}