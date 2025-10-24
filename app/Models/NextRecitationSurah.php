<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class NextRecitationSurah extends Pivot
{
    protected $table = 'next_recitation_surah';

    protected $fillable = [
        'next_recitation_id',
        'surah_id',
        'type',
        'fromAyeh',
        'toAyeh',
        'fromPage',
        'toPage',
    ];

    protected $casts = [
        'type' => 'string',
        'fromAyeh' => 'integer',
        'toAyeh' => 'integer',
        'fromPage' => 'integer',
        'toPage' => 'integer',
    ];

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

    /**
     * Validation rules based on type
     */
    public static function rules($type)
    {
        if ($type === 'ayah') {
            return [
                'fromAyeh' => 'required|integer|min:1',
                'toAyeh' => 'required|integer|min:1|gte:fromAyeh',
            ];
        } else {
            return [
                'fromPage' => 'required|integer|min:1',
                'toPage' => 'required|integer|min:1|gte:fromPage',
            ];
        }
    }
}
