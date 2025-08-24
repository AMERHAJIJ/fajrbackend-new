<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Category extends Model
{
    protected $guarded = [];

    /**
     * Get the blogs for the category.
     */
    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'category_id', 'id');
    }

    /**
     * Get the files for the category.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'object');
    }
}