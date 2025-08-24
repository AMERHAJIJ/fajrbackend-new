<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\hasOne;


class Video extends Model
{
    protected $guarded = [];

    /**
     * Get the parent model that the video belongs to.
     */
    public function object(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the quizzes for the video.
     */
    public function quizzes(): hasOne
    {
        return $this->hasOne(Quiz::class, 'video_id', 'id');
    }

    /**
     * Get the seen video records for the video.
     */
    public function seenVideos(): HasMany
    {
        return $this->hasMany(SeenVideo::class, 'video_id', 'id');
    }
}