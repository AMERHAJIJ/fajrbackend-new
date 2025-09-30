<?php

namespace App\Filament\Resources\NextRecitationResource\Pages;

use App\Filament\Resources\NextRecitationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNextRecitations extends ListRecords
{
    protected static string $resource = NextRecitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
