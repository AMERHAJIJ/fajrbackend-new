<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Get the recitation records for the surah.
     */
    public function recitationRecords(): HasMany
    {
        return $this->hasMany(RecitationRecord::class, 'surah_id', 'id');
    }
}