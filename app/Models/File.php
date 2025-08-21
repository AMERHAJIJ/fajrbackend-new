<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    protected $guarded = [];

    /**
     * Get the parent model that the file belongs to.
     */
    public function object(): MorphTo
    {
        return $this->morphTo();
    }
}