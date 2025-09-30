<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RecitationRecordSurah extends Pivot
{
    protected $table = 'recitation_record_surah';
    
    protected $fillable = [
        'recitation_record_id',
        'surah_id',
        'fromAyeh',
        'toAyeh',
    ];
    
    protected $casts = [
        'fromAyeh' => 'integer',
        'toAyeh' => 'integer',
    ];
}
