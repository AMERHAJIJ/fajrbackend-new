<?php

namespace App\Filament\Resources\VideoAnalyticResource\Pages;

use App\Filament\Resources\VideoAnalyticResource;
use Filament\Resources\Pages\ListRecords;

class ListVideoAnalytics extends ListRecords
{
    protected static string $resource = VideoAnalyticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
