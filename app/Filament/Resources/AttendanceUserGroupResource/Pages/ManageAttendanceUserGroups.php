<?php

namespace App\Filament\Resources\AttendanceUserGroupResource\Pages;

use App\Filament\Resources\AttendanceUserGroupResource;
use App\Models\UserAttGroup;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageAttendanceUserGroups extends ManageRecords
{
    protected static string $resource = AttendanceUserGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->using(function (array $data): Model {
                $q = UserAttGroup::find($data['user_att_group_id']);
                $q->userTeams()->attach($data['user_id']);
                return $q;
            }),
        ];
    }
}
