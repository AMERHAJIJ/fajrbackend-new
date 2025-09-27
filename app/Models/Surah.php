<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Surah extends Model
{
    protected $guarded = [];

    /**
     * Get the next recitation records for the surah.
     */
    public function nextRecitations(): HasMany
    {
        return $this->hasMany(NextRecitation::class, 'surah_id', 'id');
    }

    /**
     * The recitation records that belong to the surah.
     */
    public function recitationRecords(): BelongsToMany
    {
        return $this->belongsToMany(RecitationRecord::class, 'recitation_record_surah')
            ->using(RecitationRecordSurah::class)
            ->withPivot(['fromAyeh', 'toAyeh'])
            ->withTimestamps();
    }
}