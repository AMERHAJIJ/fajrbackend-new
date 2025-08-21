<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $guarded = [];

    /**
     * Get the attendance records for the subject.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'subject_id', 'id');
    }

    /**
     * Get the recitation records for the subject.
     */
    public function recitationRecords(): HasMany
    {
        return $this->hasMany(RecitationRecord::class, 'surah_id', 'id');
    }
    
    /**
     * Get the students for the subject.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_subjects', 'subject_id', 'student_id');
    }

    public function teachers(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'subject_teachers', 'subject_id', 'teacher_id');
}
}