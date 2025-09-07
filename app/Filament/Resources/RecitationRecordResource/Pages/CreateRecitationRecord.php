<?php

namespace App\Filament\Resources\RecitationRecordResource\Pages;

use App\Filament\Resources\RecitationRecordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRecitationRecord extends CreateRecord
{
    protected static string $resource = RecitationRecordResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
