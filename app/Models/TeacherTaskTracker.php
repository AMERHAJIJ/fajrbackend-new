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
     * Get attendance stats for the tracker
     */
    public function getAttendanceStats(): array
    {
        $total = User::role('student')
            ->whereHas('subjectsAsStudent', function($q) {
                $q->where('subject_id', $this->subject_id);
            })->count();
            
        $present = \App\Models\Attendance::where('subject_id', $this->subject_id)
            ->whereDate('date', $this->date)
            ->count();
            
        return [
            'completed' => $present,
            'total' => $total,
            'text' => "{$present} / {$total}",
            'percentage' => $total > 0 ? min(100, round(($present / $total) * 100)) : 0
        ];
    }

    /**
     * Get recitation stats for the tracker
     */
    public function getRecitationStats(): array
    {
        $total = User::role('student')
            ->whereHas('subjectsAsStudent', function($q) {
                $q->where('subject_id', $this->subject_id);
            })->count();
            
        $recited = \App\Models\RecitationRecord::where('subject_id', $this->subject_id)
            ->whereDate('date', $this->date)
            ->count();
            
        return [
            'completed' => $recited,
            'total' => $total,
            'text' => "{$recited} / {$total}",
            'percentage' => $total > 0 ? min(100, round(($recited / $total) * 100)) : 0
        ];
    }

    /**
     * Get next recitation stats for the tracker
     */
    public function getNextRecitationStats(): array
    {
        $total = User::role('student')
            ->whereHas('subjectsAsStudent', function($q) {
                $q->where('subject_id', $this->subject_id);
            })->count();
            
        $set = \App\Models\NextRecitation::where('subject_id', $this->subject_id)
            ->whereDate('created_at', $this->date)
            ->count();
            
        return [
            'completed' => $set,
            'total' => $total,
            'text' => "{$set} / {$total}",
            'percentage' => $total > 0 ? min(100, round(($set / $total) * 100)) : 0
        ];
    }

    /**
     * Get homework stats for the tracker
     */
    public function getHomeworkStats(): array
    {
        $sent = \App\Models\Homework::where('subject_id', $this->subject_id)
            ->whereDate('created_at', $this->date)
            ->exists();
            
        return [
            'completed' => $sent ? 1 : 0,
            'total' => 1,
            'text' => $sent ? 'تم الإرسال' : 'لم يرسل',
            'percentage' => $sent ? 100 : 0
        ];
    }

    /**
     * Get whatsapp stats for the tracker
     */
    public function getWhatsappStats(): array
    {
        $sent = $this->whatsapp_sent;
        return [
            'completed' => $sent ? 1 : 0,
            'total' => 1,
            'text' => $sent ? 'تم الإرسال' : 'لم يرسل',
            'percentage' => $sent ? 100 : 0
        ];
    }

    /**
     * Get completion percentage for the day
     */
    public function getCompletionPercentageAttribute(): int
    {
        $stats = [
            $this->getAttendanceStats()['percentage'],
            $this->getRecitationStats()['percentage'],
            $this->getNextRecitationStats()['percentage'],
            $this->getHomeworkStats()['percentage'],
            $this->getWhatsappStats()['percentage'],
        ];
        return round(array_sum($stats) / count($stats));
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
