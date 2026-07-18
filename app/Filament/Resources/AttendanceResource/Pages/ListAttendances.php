<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with(['student', 'subject'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('إضافة حضور جديد')
                ->icon('heroicon-o-plus')
        ];
    }
}
