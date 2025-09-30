<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف الدور'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // حفظ الصلاحيات منفصلة
        if (isset($data['permissions'])) {
            $this->record->syncPermissions($data['permissions']);
            unset($data['permissions']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // تأكيد حفظ الصلاحيات
        if ($this->data['permissions'] ?? null) {
            $permissions = Permission::whereIn('id', $this->data['permissions'])->get();
            $this->record->syncPermissions($permissions);
        }
    }
}
