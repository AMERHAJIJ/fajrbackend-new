<?php

namespace App\Filament\Resources\HomeworkResource\Actions;

use App\Models\User;
use App\Services\TeacherTaskAutoTracker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\Action;

class ExportHomeworkToWhatsApp
{
    public static function make(): Action
    {
        return Action::make('exportToWhatsApp')
            ->label('واتساب')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('success')
            ->tooltip('مشاركة الواجب عبر واتساب')
            ->action(function ($record, \Livewire\Component $livewire) {
                // تحديث مهمة إرسال الواتساب تلقائياً عند الضغط على الزر
                \App\Services\TaskTrackingService::track(
                    $record->teacher_id ?? auth()->id(),
                    $record->subject_id,
                    'whatsapp_sent'
                );
                
                $message = self::formatWhatsAppMessage($record);
                $encodedMessage = urlencode($message);
                $whatsappUrl = "https://wa.me/?text={$encodedMessage}";
                
                $livewire->js("window.open('{$whatsappUrl}', '_blank')");
            });
    }
//description
    protected static function formatWhatsAppMessage($homework): string
    {
        $message = "*النادي الصيفي 2026 - فصبر جميل*\n";
        $message .= "السلام عليكم ورحمة الله وبركاته 🌹\n";
        $message .= "أهالينا الكرام، نضع بين أيديكم الواجب المنزلي الجديد لطلابنا الأعزاء:\n\n";
        
        $message .= "🏫 *الحلقة:* " . ($homework->subject ? $homework->subject->title : '') . "\n";
        $message .= "📖 *الدرس:* {$homework->lesson_name}\n";
        $message .= "📌 *العنوان:* {$homework->title}\n";
        
        if (!empty($homework->page_number)) {
            $message .= "🔢 *الصفحة المطلوبة:* {$homework->page_number}\n";
        }
        
        if (!empty($homework->description)) {
            $message .= "\n📝 *أهداف وفوائد الدرس:*\n{$homework->description}\n";
        }
        
        if ($homework->due_date) {
            $message .= "\n⏰ *آخر موعد للتسليم:* {$homework->due_date->format('Y-m-d')}\n";
        }
        
        $message .= "-----------------------------------------\n";
        $message .= "نرجو منكم متابعة أولادكم وتشجيعهم على أداء الواجب بهمة وإتقان.\n";
        $message .= "جزاكم الله خيراً وبارك بجهودكم 🌸✨";
        
        return $message;
    }
}
