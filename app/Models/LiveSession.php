<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'active'     => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Check if the session is upcoming (not started yet)
     */
    public function isUpcoming(): bool
    {
        return now()->lt($this->start_time);
    }

    /**
     * Check if the session is currently live
     */
    public function isLive(): bool
    {
        return now()->between($this->start_time, $this->end_time);
    }

    /**
     * Check if the session has ended
     */
    public function hasEnded(): bool
    {
        return now()->gt($this->end_time);
    }

    /**
     * Get status label
     */
    public function getStatusAttribute(): string
    {
        if ($this->isLive()) return 'live';
        if ($this->isUpcoming()) return 'upcoming';
        return 'ended';
    }

    /**
     * Get duration in minutes
     */
    public function getDurationInMinutesAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }
}
