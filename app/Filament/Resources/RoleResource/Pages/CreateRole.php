<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // حفظ الصلاحيات بعد إنشاء الدور
        if ($this->data['permissions'] ?? null) {
            $permissions = Permission::whereIn('id', $this->data['permissions'])->get();
            $this->record->syncPermissions($permissions);
        }
    }
}
