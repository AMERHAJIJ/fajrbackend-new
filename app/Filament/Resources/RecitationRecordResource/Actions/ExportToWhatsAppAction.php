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
                        $query = User::role('student');

                        if ($user->hasRole('teacher')) {
                            $query->whereHas('subjectsAsStudent', function ($q) use ($user) {
                                $q->whereHas('teachers', function ($q) use ($user) {
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
                $message .= "الأهالي الكرام 🌸🌸\n";
                $message .= "حياكم الله وعسى أن تكونوا بخير 🌷\n\n";
                $message .= "سيتم إرسال تسميع الطلاب ليوم *" . self::getArabicDayName($today) . "*\n";
                $message .= "_التاريخ:_ " . $today->day . '/' . $today->month . "\n\n";
                $message .= "نشكر لكم حسن المتابعة 🌿\n";
                $message .= "دمتم في رعاية الله 🤲🏻🌸\n\n";

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

                    // ✅ إضافة حالة الحضور بالرموز
                    $attendanceStatus = $student->recitationRecords->isNotEmpty()
                        ? (($student->recitationRecords->first()->is_present ?? true) ? '✅ حاضر' : '❌ غائب')
                        : '❌ غائب';

                    $message .= "حالة الحضور: {$attendanceStatus}\n\n";
                }

                $message .= "----------------------------------------------------------\n";
                $message .= "نشكر لكم حسن المتابعة والحرص على الأبناء، ونسأل الله أن يجعلهم من حفظة كتابه الكريم 🤲🏻\n\n";

                if (!empty($data['notes'])) {
                    $message .= "*ملاحظات إضافية:*\n{$data['notes']}\n\n";
                }

                // تحديث سجل المعلم في التتبع
                $teacherId = Auth::id();
                $date = now()->toDateString();

                if ($teacherId && $students->isNotEmpty()) {
                    $firstStudent = $students->first();
                    if ($firstStudent && $firstStudent->subjectsAsStudent->isNotEmpty()) {
                        $teacherSubjects = $firstStudent->subjectsAsStudent->filter(function ($subject) use ($teacherId) {
                            return $subject->teachers->contains('id', $teacherId);
                        });

                        if ($teacherSubjects->isNotEmpty()) {
                            $subjectId = $teacherSubjects->first()->id;
                            TeacherTaskAutoTracker::updateWhatsAppTask($teacherId, $subjectId, $date);
                        }
                    }
                }

                // تنسيق الرسالة لواتساب
                $message = str_replace("\n", "%0A", $message);
                $whatsappUrl = "https://wa.me/?text={$message}";
                return redirect($whatsappUrl);
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
