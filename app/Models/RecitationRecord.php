<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecitationRecord  extends Model
{
    protected $guarded = [];

    /**
     * Get the student that owns the recitation record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    /**
     * Get the surah that the recitation record belongs to.
     */
    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class, 'surah_id', 'id');
    }
}