<?php

namespace App\Filament\Resources\HomeworkResource\Actions;

use App\Models\User;
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
            ->form([
                Textarea::make('notes')
                    ->label('ملاحظات إضافية')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, $record) {
                $message = self::formatWhatsAppMessage($record, $data);
                $encodedMessage = urlencode($message);
                $whatsappUrl = "https://wa.me/?text={$encodedMessage}";
                
                return redirect()->away($whatsappUrl);
            });
    }
//description
    protected static function formatWhatsAppMessage($homework, array $data): string
    {
        $message = "📚 *معلومات الواجب* 📚\n\n";
        $message .= "📚 *المادة:* {$homework->subject->title}\n";
        $message .= "📖 *الدرس:* {$homework->lesson_name}\n";
        $message .= "📌 *العنوان:* {$homework->title}\n";
        $message .= "🔢 *رقم الصفحة:* {$homework->page_number}\n";
        
        if (!empty($homework->description)) {
            $message .= "\n📝 *فوائد الدرس :*\n{$homework->description}\n";
        }
        
        if ($homework->due_date) {
            $message .= "\n⏰ *موعد التسليم:* {$homework->due_date->format('Y-m-d')}\n";
        }
        
        if (!empty($data['notes'])) {
            $message .= "\n*ملاحظات إضافية:*\n{$data['notes']}\n";
        }
        
        return $message;
    }
}
