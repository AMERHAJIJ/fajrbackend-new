<?php

namespace App\Filament\Resources\RecitationRecordResource\Actions;

use App\Models\User;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Http;
use App\Services\TeacherTaskAutoTracker;
use Illuminate\Support\Facades\Auth;

class ExportToWhatsAppAction
{
    public static function make(): Action
    {
        $user = Auth::user();
        
        if (!$user) {
            return Action::make('exportToWhatsApp')
                ->label('واتساب')
                ->disabled()
                ->tooltip('يجب تسجيل الدخول أولاً');
        }
        
        return Action::make('exportToWhatsApp')
            ->label('واتساب')
            ->color('success')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->tooltip('مشاركة تقرير التسميع عبر واتساب')
            ->form([
                Forms\Components\Select::make('students')
                    ->label('اختر الطلاب')
                    ->options(function () use ($user) {
                        $query = User::role('student');
                        
                        // If user is teacher, filter their students
                        if ($user->hasRole('teacher')) {
                            $query->whereHas('subjectsAsStudent', function($q) use ($user) {
                                $q->whereHas('teachers', function($q) use ($user) {
                                    $q->where('users.id', $user->id);
                                });
                            });
                        }
                        
                        return $query->pluck('name', 'id');
                    })
                    ->searchable()
                    ->multiple()
                    ->required()
                    ->preload(),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات إضافية')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data) {
                // Get students with their recitation data and next recitation
                $students = User::with([
                    'recitationRecords' => function($query) {
                        $query->with(['surahs'])
                            ->latest()
                            ->take(5); // Last 5 records
                    },
                    'nextRecitations' => function($query) {
                        $query->with('surah')->latest()->take(1);
                    }
                ])->whereIn('id', $data['students'])->get();

                // Generate message with the requested format
                $today = now();
                $nextWeek = now()->addWeek();
                $message = "السلام عليكم ورحمة الله وبركاته\n\n";
                $message .= "الأهالي الكرام 🌸🌸\n";
                $message .= "حياكم الله وعسى أن تكونوا بخير 🌷\n\n";
                $message .= "سيتم إرسال تسميع الطلاب ليوم *" . self::getArabicDayName($today) . "*\n";
                $message .= $today->day . '/' . $today->month . "\n";
                $message .= "نشكر لكم حسن المتابعة\n";
                $message .= "دمتم في رعاية الله 🤲🏻🌸\n\n";
                
                foreach ($students as $student) {
                    $message .= "----------------------------------------------------------\n";
                    $message .= "*اسم الطالب :* {$student->name}\n";
                    
                    if ($student->recitationRecords->isNotEmpty()) {
                        $latestRecord = $student->recitationRecords->first();
                        $surahs = $latestRecord->surahs->map(function($surah) {
                            return "{$surah->name} (من آية {$surah->pivot->fromAyeh} إلى {$surah->pivot->toAyeh})";
                        })->implode('، ');
                        
                        $message .= "انجاز اليوم : {$surahs}\n";
                        $message .= "التقييم من 10 : {$latestRecord->score}/10\n";
                    } else {
                        $message .= "انجاز اليوم : -\n";
                        $message .= "التقييم من 10 : -\n";
                    }
                    
                    // Next recitation assignment
                    if ($student->nextRecitations->isNotEmpty()) {
                        $nextRecitation = $student->nextRecitations->first();
                        $nextSurah = $nextRecitation->surah;
                        $message .= sprintf(
                            "واجب الدرس القادم : %s (من آية %d إلى %d)\n",
                            $nextSurah->name,
                            $nextRecitation->fromAyeh,
                            $nextRecitation->toAyeh
                        );
                        $message .= "موعد التسميع : " . self::getArabicDayName($nextWeek) . " الموافق {$nextWeek->format('d/m')}\n";
                    } else {
                        $nextLessonDate = $nextWeek->format('d/m');
                        $message .= "واجب الدرس القادم : سيتم تحديده لاحقاً\n";
                        $message .= "موعد التسميع : " . self::getArabicDayName($nextWeek) . " الموافق {$nextLessonDate}\n";
                    }
                }
                
                $message .= "\n----------------------------------------------------------\n";
                $message .= "نشكر لكم حسن المتابعة والحرص على الأبناء، ونسأل الله أن يجعلهم من حفظة كتابه الكريم 🤲";
                
                // Add notes if any
                if (!empty($data['notes'])) {
                    $message = str_replace("نشكر لكم حسن المتابعة والحرص", "*ملاحظات إضافية:*\n{$data['notes']}\n\nنشكر لكم حسن المتابعة والحرص", $message);
                }
                
                // Auto-update teacher task tracker for WhatsApp
                $teacherId = Auth::id();
                $date = now()->toDateString();
                
                // Get subject ID from the first student's subjects
                if ($teacherId && $students->isNotEmpty()) {
                    $firstStudent = $students->first();
                    if ($firstStudent && $firstStudent->subjectsAsStudent->isNotEmpty()) {
                        // Find the subject that the teacher teaches
                        $teacherSubjects = $firstStudent->subjectsAsStudent->filter(function ($subject) use ($teacherId) {
                            return $subject->teachers->contains('id', $teacherId);
                        });
                        
                        if ($teacherSubjects->isNotEmpty()) {
                            $subjectId = $teacherSubjects->first()->id;
                            TeacherTaskAutoTracker::updateWhatsAppTask($teacherId, $subjectId, $date);
                        }
                    }
                }
                
                // Create WhatsApp share link
                $whatsappUrl = 'https://wa.me/?text=' . urlencode($message);
                
                // Return the URL to open in a new tab
                return redirect($whatsappUrl);
            });
    }
    
    /**
     * Get Arabic day name
     */
    private static function getArabicDayName($date)
    {
        $days = [
            'Sunday' => 'الأحد',
            'Monday' => 'الإثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت',
        ];
        
        return $days[$date->format('l')];
    }
}
