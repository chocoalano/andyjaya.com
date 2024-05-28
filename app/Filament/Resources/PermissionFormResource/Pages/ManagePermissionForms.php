<?php

namespace App\Filament\Resources\PermissionFormResource\Pages;

use App\Filament\Resources\PermissionFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePermissionForms extends ManageRecords
{
    protected static string $resource = PermissionFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {
                $data['status_hr'] = 'waiting';
                return $data;
            }),
        ];
    }
}
