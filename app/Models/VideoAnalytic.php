<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoAnalytic extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_anomaly'   => 'boolean',
        'anomaly_score' => 'float',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'video_id');
    }
}
