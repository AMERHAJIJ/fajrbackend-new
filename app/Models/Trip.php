<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'departure_time' => 'datetime',
        'return_time' => 'datetime',
    ];

    public function buses(): HasMany
    {
        return $this->hasMany(TripBus::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TripParticipant::class);
    }

    // Accessors for calculated costs
    public function getTotalCostAttribute()
    {
        return ($this->bus_cost ?? 0) + ($this->food_cost ?? 0) + ($this->entry_cost ?? 0) + ($this->additional_cost ?? 0) + ($this->other_expenses ?? 0);
    }

    public function getExpectedRevenueAttribute()
    {
        $confirmedCount = $this->participants()->where('status', 'confirmed')->count();
        return $confirmedCount * ($this->cost_per_student ?? 0);
    }

    public function getCollectedPaymentsAttribute()
    {
        return $this->participants()->where('status', 'confirmed')->sum('paid_amount');
    }
}
