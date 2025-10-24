<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NextRecitation extends Model
{
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'type' => 'string',
        'fromAyeh' => 'integer',
        'toAyeh' => 'integer',
        'fromPage' => 'integer',
        'toPage' => 'integer',
    ];

    /**
     * Get the student that owns the next recitation record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id', 'id');
    }

    /**
     * Get the surahs for the next recitation.
     */
    public function surahs(): BelongsToMany
    {
        return $this->belongsToMany(Surah::class, 'next_recitation_surah')
            ->using(NextRecitationSurah::class)
            ->withPivot(['type', 'fromAyeh', 'toAyeh', 'fromPage', 'toPage'])
            ->withTimestamps();
    }

    /**
     * Get the range attribute based on type
     */
    public function getRangeAttribute()
    {
        if ($this->type === 'ayah') {
            return "من آية {$this->fromAyeh} إلى {$this->toAyeh}";
        } else {
            return "من صفحة {$this->fromPage} إلى {$this->toPage}";
        }
    }

    /**
     * Get the display type attribute
     */
    public function getDisplayTypeAttribute()
    {
        return $this->type === 'ayah' ? 'آيات' : 'صفحات';
    }
}