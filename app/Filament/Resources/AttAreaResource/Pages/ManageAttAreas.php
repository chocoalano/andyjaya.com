<?php

namespace App\Filament\Resources\AttAreaResource\Pages;

use App\Filament\Resources\AttAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAttAreas extends ManageRecords
{
    protected static string $resource = AttAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
