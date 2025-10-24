<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RecitationRecord extends Model
{
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'score' => 'decimal:2',
    ];

    /**
     * Get the student that owns the recitation record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    /**
     * The surahs that belong to the recitation record.
     */
    public function surahs(): BelongsToMany
    {
        return $this->belongsToMany(Surah::class, 'recitation_record_surah')
            ->using(RecitationRecordSurah::class)
            ->withPivot(['type', 'fromAyeh', 'toAyeh', 'fromPage', 'toPage'])
            ->withTimestamps();
    }
}