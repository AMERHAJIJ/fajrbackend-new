<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripParticipant extends Model
{
    protected $guarded = [];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(TripBus::class, 'trip_bus_id');
    }

    public function getRemainingAmountAttribute()
    {
        if (!$this->trip) return 0;
        return max(0, ($this->trip->cost_per_student ?? 0) - ($this->paid_amount ?? 0));
    }
}
