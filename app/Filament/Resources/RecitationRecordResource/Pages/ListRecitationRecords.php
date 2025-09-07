<?php

namespace App\Filament\Resources\RecitationRecordResource\Pages;

use App\Filament\Resources\RecitationRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRecitationRecords extends ListRecords
{
    protected static string $resource = RecitationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة تسجيل تلاوة جديد'),
        ];
    }
}
