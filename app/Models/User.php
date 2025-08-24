<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

   
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id', 'id');
    }

    /**
     * Get the next recitation records for the student.
     */
    public function nextRecitations(): HasMany
    {
        return $this->hasMany(NextRecitation::class, 'student_id', 'id');
    }

    /**
     * Get the recitation records for the student.
     */
    public function recitationRecords(): HasMany
    {
        return $this->hasMany(RecitationRecord::class, 'student_id', 'id');
    }

    /**
     * Get the seen video records for the student.
     */
    public function seenVideos(): HasMany
    {
        return $this->hasMany(SeenVideo::class, 'student_id', 'id');
    }

    /**
     * Get the student answers for the quiz.
     */
    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'student_id', 'id');
    }

    /**
     * Get the student quiz records.
     */
    public function studentQuizzes(): HasMany
    {
        return $this->hasMany(StudentQuiz::class, 'student_id', 'id');
    }
    
    /**
     * Get the subjects for the student.
     */
    public function subjectsAsStudent(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subjects', 'student_id', 'subject_id');
    }

    /**
     * Get the subjects for the teacher.
     */
    public function subjectsAsTeacher(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_teachers', 'teacher_id', 'subject_id');
    }
}