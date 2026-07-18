<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripBus extends Model
{
    protected $guarded = [];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TripParticipant::class, 'trip_bus_id');
    }

    public function getEmptySeatsAttribute()
    {
        $taken = $this->participants()->count();
        return max(0, ($this->capacity ?? 0) - $taken);
    }
}
