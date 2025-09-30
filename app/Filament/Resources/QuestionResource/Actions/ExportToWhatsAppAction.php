<?php

namespace App\Filament\Resources\QuestionResource\Actions;

use App\Models\User;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class ExportToWhatsAppAction
{
    public static function make(): Action
    {
        $user = auth()->user();
        
        return Action::make('exportToWhatsApp')
            ->label('واتساب')
            ->color('success')
            ->tooltip('مشاركة تقرير التسميع عبر واتساب')
            ->form([
                \Filament\Forms\Components\Select::make('students')
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
                // Get students with their recitation data
                $students = User::with([
                    'recitationRecords' => function($query) {
                        $query->with('surah')
                            ->latest()
                            ->take(1);
                    },
                    'nextRecitations' => function($query) {
                        $query->with('surah')
                            ->latest()
                            ->take(1);
                    }
                ])
                ->whereIn('id', $data['students'])
                ->get();
                
                // Format the message
                $message = self::formatWhatsAppMessage($students, $data);
                
                $encodedMessage = urlencode($message);
                $whatsappUrl = "https://wa.me/?text={$encodedMessage}";
                
                return redirect()->away($whatsappUrl);
            });
    }
    
    protected static function formatWhatsAppMessage($students, array $data): string
    {
        \Carbon\Carbon::setLocale('ar');
        $today = now();
        $dayNumber = $today->day;
        $dayName = $today->translatedFormat('l');
        $month = $today->translatedFormat('n');
        
        $message  = "السلام عليكم ورحمة الله وبركاته\n\n";
        $message .= "الأهالي الكرام 🌸🌸\n";
        $message .= "حياكم الله وعسى أن تكونوا بخير 🌷\n\n";
        $message .= "*تقرير متابعة التلاوة والحفظ*\n";
        $message .= "📅 تاريخ التقرير: $dayName $dayNumber/$month\n\n";

        foreach ($students as $student) {
            $latestRecord = $student->recitationRecords->first();

            $message .= "👤 *اسم الطالب:* {$student->name}\n";
            
            if ($latestRecord) {
                $message .= "📖 *آخر ما تم تسميعه:*\n";
                $message .= "- السورة: {$latestRecord->surah->name}\n";
                $message .= "- من آية: {$latestRecord->fromAyeh}\n";
                $message .= "- إلى آية: {$latestRecord->toAyeh}\n";
                if ($latestRecord->notes) {
                    $message .= "- ملاحظات التسميع: {$latestRecord->notes}\n";
                }
                
                $nextRecitation = $student->nextRecitations->first();
                if ($nextRecitation) {
                    $message .= "\n📚 *الواجب القادم:*\n";
                    $message .= "- السورة: " . ($nextRecitation->surah->name ?? 'غير محدد') . "\n";
                    $message .= "- من آية: " . ($nextRecitation->fromAyeh ?? 'غير محدد') . "\n";
                    $message .= "- إلى آية: " . ($nextRecitation->toAyeh ?? 'غير محدد') . "\n";
                    if (!empty($nextRecitation->notes)) {
                        $message .= "- ملاحظات: {$nextRecitation->notes}\n";
                    }
                }
            } else {
                $message .= "⚠️ لا توجد تسجيلات تسميع سابقة\n";
            }
            
            $message .= "\n";
        }

        if (!empty($data['notes'])) {
            $message .= "*ملاحظات إضافية:*\n";
            $message .= $data['notes'] . "\n\n";
        }

        $message .= "\n";
        $message .= "نشكر لكم حسن المتابعة والحرص على الأبناء، \n";
        $message .= "ونسأل الله أن يجعلهم من حفظة كتابه الكريم 🤍";

        return $message;
    }
}
