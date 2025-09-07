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

    /**
     * Get the student subject records for the subject.
     */
    public function studentSubjects(): HasMany
    {
        return $this->hasMany(StudentSubject::class, 'subject_id', 'id');
    }

    /**
     * Get the subject teacher records for the subject.
     */
    public function subjectTeachers(): HasMany
    {
        return $this->hasMany(SubjectTeacher::class, 'subject_id', 'id');
    }

    /**
     * Get the homeworks for the subject.
     */
    public function homeworks(): HasMany
    {
        return $this->hasMany(Homework::class, 'subject_id', 'id');
    }
}