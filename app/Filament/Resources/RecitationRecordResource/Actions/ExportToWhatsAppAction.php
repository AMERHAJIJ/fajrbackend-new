<?php

namespace App\Filament\Resources\RecitationRecordResource\Actions;

use App\Models\User;
use App\Services\TeacherTaskAutoTracker;
use Filament\Forms;
use Filament\Tables\Actions\Action;
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
                        $query = User::role('student')
                            ->whereHas('recitationRecords', function ($q) use ($user) {
                                $q->where('teacher_id', $user->id)
                                  ->whereDate('date', now());
                            });

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
            ->action(function (array $data, $livewire) {
                $students = User::with([
                    'recitationRecords' => function ($query) {
                        $query->with(['surahs'])
                            ->latest()
                            ->take(5);
                    },
                    'nextRecitations' => function ($query) {
                        $query->with('surahs')->latest()->take(1);
                    },
                    'subjectsAsStudent.teachers'
                ])->whereIn('id', $data['students'])->get();

                $today = now();
                $nextWeek = now()->addWeek();

                $message = "*السلام عليكم ورحمة الله وبركاته*\n\n";
                $message .= "الأهالي الكرام 🌹\n";
                $message .= "حياكم الله وعسى أن تكونوا بخير 🌸\n\n";
                $message .= "سيتم إرسال تسميع الطلاب ليوم *" . self::getArabicDayName($today) . "*\n";
                $message .= "_التاريخ:_ " . $today->day . '/' . $today->month . "\n\n";
                $message .= "نشكر لكم حسن المتابعة 🌸\n";
                $message .= "دمتم في رعاية الله 🤲✨\n\n";

                foreach ($students as $student) {
                    $message .= "----------------------------------------------------------\n";
                    $message .= "*اسم الطالب:* {$student->name}\n";

                    if ($student->recitationRecords->isNotEmpty()) {
                        $latestRecord = $student->recitationRecords->first();
                        $surahs = $latestRecord->surahs->map(function ($surah) {
                            if ($surah->pivot->type === 'ayah') {
                                return "{$surah->name} (من آية {$surah->pivot->fromAyeh} إلى {$surah->pivot->toAyeh})";
                            } else {
                                return "{$surah->name} (من صفحة {$surah->pivot->fromPage} إلى {$surah->pivot->toPage})";
                            }
                        })->implode('، ');

                        $message .= "إنجاز اليوم: {$surahs}\n";
                        $message .= "التقييم من 10: *{$latestRecord->score}/10*\n";
                    } else {
                        $message .= "إنجاز اليوم: -\n";
                        $message .= "التقييم من 10: -\n";
                    }

                    if ($student->nextRecitations->isNotEmpty()) {
                        $nextRecitation = $student->nextRecitations->first();
                        if ($nextRecitation->surahs->isNotEmpty()) {
                            $surahs = $nextRecitation->surahs->map(function ($surah) {
                                if ($surah->pivot->type === 'ayah') {
                                    return "{$surah->name} (من آية {$surah->pivot->fromAyeh} إلى {$surah->pivot->toAyeh})";
                                } else {
                                    return "{$surah->name} (من صفحة {$surah->pivot->fromPage} إلى {$surah->pivot->toPage})";
                                }
                            })->implode('، ');
                            
                            $message .= "واجب الدرس القادم: {$surahs}\n";
                        } else {
                            $message .= "واجب الدرس القادم: سيتم تحديده لاحقاً\n";
                        }
                    } else {
                        $message .= "واجب الدرس القادم: سيتم تحديده لاحقاً\n";
                    }

                    // ✅ إضافة حالة الحضور بالرموز من جدول الحضور
                    $todayAttendance = \App\Models\Attendance::where('student_id', $student->id)
                        ->whereDate('date', $today)
                        ->first();
                    
                    if ($todayAttendance) {
                        $attendanceStatus = $todayAttendance->status ? '🟢 حاضر' : '🔴 غائب';
                    } else {
                        $attendanceStatus = $student->recitationRecords->isNotEmpty() ? '🟢 حاضر' : '🔴 غائب';
                    }

                    $message .= "حالة الحضور: {$attendanceStatus}\n\n";
                }

                $message .= "----------------------------------------------------------\n";
                $message .= "نشكر لكم حسن المتابعة والحرص على الأبناء، ونسأل الله أن يجعلهم من حفظة كتابه الكريم ❤️\n\n";

                if (!empty($data['notes'])) {
                    $message .= "*ملاحظات إضافية:*\n{$data['notes']}\n\n";
                }

                // تحديث سجل المعلم في التتبع تلقائياً باستخدام الخيار الذكي
                $teacherId = Auth::id();
                $quranSubject = \App\Models\Subject::where('is_quran', true)->first();
                $subjectId = $quranSubject ? $quranSubject->id : null;

                if ($teacherId && $subjectId) {
                    \App\Services\TaskTrackingService::track($teacherId, $subjectId, 'whatsapp_sent');
                }

                // ترميز الرسالة بشكل آمن للروابط لضمان ظهور الإيموجيات
                $whatsappUrl = "https://wa.me/?text=" . rawurlencode($message);
                $livewire->js("window.open('{$whatsappUrl}', '_blank')");
            });
    }

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
