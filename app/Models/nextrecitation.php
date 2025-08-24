<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NextRecitation extends Model
{
    protected $guarded = [];

    /**
     * Get the student that owns the next recitation record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    /**
     * Get the surah that the next recitation record belongs to.
     */
    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class, 'surah_id', 'id');
    }
}