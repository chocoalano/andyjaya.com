<?php

namespace App\Filament\Resources\FormPaidLeaveResource\Pages;

use App\Filament\Resources\FormPaidLeaveResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageFormPaidLeaves extends ManageRecords
{
    protected static string $resource = FormPaidLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {
                $data['status_line'] = 'waiting';
                $data['status_mngr'] = 'waiting';
                $data['status_hr'] = 'waiting';
                return $data;
            })
            ->using(function (array $data, string $model): Model {
                $user = User::find($data['user_id']);
                $notify = [$user->approval_line, $user->approval_manager, $user->approval_hr];
                $notifyTo = User::whereIn('id', $notify)->get();
                Notification::make()
                            ->title('Paid leave successfully')
                            ->success()
                            ->broadcast($notifyTo)
                            ->sendToDatabase($notifyTo);
                return $model::create($data);
            }),
        ];
    }
}
