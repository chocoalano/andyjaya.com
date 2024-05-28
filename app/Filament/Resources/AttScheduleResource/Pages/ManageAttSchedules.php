<?php

namespace App\Filament\Resources\AttScheduleResource\Pages;

use App\Filament\Resources\AttScheduleResource;
use App\Models\UserAttGroupSchedule;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageAttSchedules extends ManageRecords
{
    protected static string $resource = AttScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->using(function (array $data): Model {
                $model = null;
                foreach ($data['dateset'] as $k) {
                    $model = UserAttGroupSchedule::create([
                        'user_att_group_id' => $data['user_att_group_id'],
                        'att_time_id' => $data['att_time_id'],
                        'date_work' => $k['date_work'],
                        'created_at' => Carbon::now(),
                    ]);
                }
                return $model;
            }),
        ];
    }
}
