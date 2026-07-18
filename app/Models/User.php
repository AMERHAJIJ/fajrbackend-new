<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        // Allow the main admin (ID 1) OR anyone with 'admin' or 'teacher' roles
        return $this->id === 1 || $this->hasAnyRole(['admin', 'teacher']);
    }

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

    // --- نظام التوكينز اليدوي (لتجاوز أخطاء Sanctum) ---
    public function tokens()
    {
        // نستخدم جدول التوكينز الخاص بـ Sanctum لكن بدون استدعاء الكلاس الخاص بهم
        return $this->morphMany(\Illuminate\Database\Eloquent\Model::class, 'tokenable', 'personal_access_tokens');
    }

    public function createToken(string $name, array $abilities = ['*'], \DateTimeInterface $expiresAt = null)
    {
        $plainTextToken = \Illuminate\Support\Str::random(40);
        
        // نقوم بإنشاء السجل في قاعدة البيانات يدوياً
        $token = \Illuminate\Support\Facades\DB::table('personal_access_tokens')->insertGetId([
            'tokenable_type' => get_class($this),
            'tokenable_id' => $this->id,
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => json_encode($abilities),
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (object) [
            'accessToken' => $token,
            'plainTextToken' => $token . '|' . $plainTextToken
        ];
    }
    // -----------------------------------------------

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id', 'id');
    }

    public function nextRecitations(): HasMany
    {
        return $this->hasMany(NextRecitation::class, 'student_id', 'id');
    }

    public function recitationRecords(): HasMany
    {
        return $this->hasMany(RecitationRecord::class, 'student_id', 'id');
    }

    public function seenVideos(): HasMany
    {
        return $this->hasMany(SeenVideo::class, 'student_id', 'id');
    }

    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class, 'student_id', 'id');
    }

    public function studentQuizzes(): HasMany
    {
        return $this->hasMany(StudentQuiz::class, 'student_id', 'id');
    }
    
    public function subjectsAsStudent(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subjects', 'student_id', 'subject_id')
            ->select('subjects.*');
    }

    public function subjectsAsTeacher(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_teachers', 'teacher_id', 'subject_id')
            ->select('subjects.*');
    }

    public function homeworks(): HasMany
    {
        return $this->hasMany(Homework::class, 'teacher_id', 'id');
    }

    // --- Financial System ---
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalCoursePaidAttribute()
    {
        return $this->payments()->where('payment_type', 'course')->sum('amount');
    }

    public function getTotalBusPaidAttribute()
    {
        return $this->payments()->where('payment_type', 'bus')->sum('amount');
    }

    public function getRemainingCourseFeeAttribute()
    {
        $globalCourseFee = \App\Models\Setting::getVal('course_fee', 0);
        return max(0, $globalCourseFee - $this->total_course_paid);
    }

    public function getRemainingBusFeeAttribute()
    {
        $globalBusFee = \App\Models\Setting::getVal('bus_fee', 0);
        return max(0, $globalBusFee - $this->total_bus_paid);
    }

    public function getFinancialStatusAttribute()
    {
        $remainingCourse = $this->remaining_course_fee;
        $remainingBus = $this->remaining_bus_fee;
        $totalRemaining = $remainingCourse + $remainingBus;
        
        $globalCourseFee = \App\Models\Setting::getVal('course_fee', 0);
        $globalBusFee = \App\Models\Setting::getVal('bus_fee', 0);
        $totalFees = $globalCourseFee + $globalBusFee;

        if ($totalFees == 0) {
            return 'not_set';
        }

        if ($totalRemaining <= 0) {
            return 'paid';
        }

        if ($totalRemaining < $totalFees) {
            return 'partial';
        }

        return 'unpaid';
    }
}