<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // إذا لم يتم تحديد أستاذ (حالة الأستاذ الذي ينشئ لنفسه)، نضع معرف المستخدم الحالي
        if (!isset($data['teacher_id'])) {
            $data['teacher_id'] = auth()->id();
        }
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
