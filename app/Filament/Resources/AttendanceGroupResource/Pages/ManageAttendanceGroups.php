<?php

namespace App\Filament\Resources\AttendanceGroupResource\Pages;

use App\Filament\Resources\AttendanceGroupResource;
use App\Models\UserAttGroup;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageAttendanceGroups extends ManageRecords
{
    protected static string $resource = AttendanceGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
