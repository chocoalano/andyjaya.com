<?php

namespace App\Filament\Resources\AttTimeResource\Pages;

use App\Filament\Resources\AttTimeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAttTimes extends ManageRecords
{
    protected static string $resource = AttTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
