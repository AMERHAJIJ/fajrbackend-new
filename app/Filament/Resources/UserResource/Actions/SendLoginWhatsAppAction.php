<?php

namespace App\Filament\Resources\UserResource\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use App\Models\User;

class SendLoginWhatsAppAction
{
    public static function make(): Action
    {
        return Action::make('sendLoginWhatsApp')
            ->label('إرسال الدخول (واتساب)')
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('إرسال بيانات الدخول عبر الواتساب')
            ->modalDescription('هل أنت متأكد أنك تريد توليد كلمة سر جديدة وإرسالها للطالب عبر الواتساب؟ (سيتم تغيير كلمة سره الحالية).')
            ->modalSubmitActionLabel('نعم، أرسل')
            ->action(function (User $record) {
                // 1. إنشاء كلمة مرور عشوائية (6 أرقام لسهولة الحفظ)
                $newPassword = mt_rand(100000, 999999);
                
                // 2. تحديث كلمة المرور في قاعدة البيانات
                $record->update([
                    'password' => Hash::make($newPassword),
                ]);

                // 3. تجهيز نص رسالة الواتساب
                $message = "السلام عليكم ورحمة الله وبركاته 🌹\n\n";
                $message .= "أهلاً بك في منصة الفجر التعليمية.\n";
                $message .= "تم تفعيل حسابك بنجاح! يمكنك الآن تسجيل الدخول إلى التطبيق باستخدام البيانات التالية:\n\n";
                $message .= "👤 *اسم المستخدم:* {$record->username}\n";
                $message .= "🔑 *كلمة المرور:* {$newPassword}\n\n";
                $message .= "📱 لتحميل التطبيق:\n";
                $message .= "https://elfajr.com/app\n\n";
                $message .= "نتمنى لك رحلة تعليمية موفقة!";

                // 4. تجهيز رقم الهاتف
                // إذا كان الرقم يبدأ ب 0 نحذفه ونضع الكود، هنا نفترض أن الرقم دولي أو نضيف كود الدولة إذا أردنا.
                $phone = $record->phone;
                // إزالة الفراغات وإشارة الزائد
                $phone = str_replace(['+', ' ', '-'], '', $phone);

                $encodedMessage = urlencode($message);
                $whatsappUrl = "https://wa.me/{$phone}?text={$encodedMessage}";
                
                Notification::make()
                    ->title('تم توليد كلمة السر، جاري فتح الواتساب...')
                    ->success()
                    ->send();

                return redirect()->away($whatsappUrl);
            });
    }
}
