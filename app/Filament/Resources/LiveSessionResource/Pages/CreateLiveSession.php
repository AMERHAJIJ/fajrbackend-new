<?php

namespace App\Filament\Resources\LiveSessionResource\Pages;

use App\Filament\Resources\LiveSessionResource;
use App\Services\GoogleMeetService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateLiveSession extends CreateRecord
{
    protected static string $resource = LiveSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-set teacher_id for teachers
        if (auth()->user()->hasRole('teacher')) {
            $data['teacher_id'] = auth()->id();
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        try {
            $meetService = app(GoogleMeetService::class);

            if (!$meetService->isAuthorized()) {
                Notification::make()
                    ->warning()
                    ->title('تنبيه')
                    ->body('لم يتم تفويض Google Meet بعد. تم حفظ الجلسة بدون رابط Meet.')
                    ->persistent()
                    ->send();
                return;
            }

            $result = $meetService->createMeeting(
                title: $record->title,
                description: $record->description ?? '',
                startTime: $record->start_time->toRfc3339String(),
                endTime: $record->end_time->toRfc3339String(),
            );

            $record->update([
                'meet_link'       => $result['meet_link'],
                'google_event_id' => $result['event_id'],
            ]);

            Notification::make()
                ->success()
                ->title('✅ تم إنشاء جلسة Google Meet بنجاح!')
                ->body('الرابط: ' . $result['meet_link'])
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('خطأ في إنشاء Google Meet')
                ->body($e->getMessage())
                ->send();
        }
    }
}
