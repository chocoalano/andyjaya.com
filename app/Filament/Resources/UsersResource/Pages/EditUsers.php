<?php

namespace App\Filament\Resources\UsersResource\Pages;

use App\Filament\Resources\UsersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EditUsers extends EditRecord
{
    protected static string $resource = UsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if(!is_null($data['password'])){
            if($data['password'] === $data['password_confirmation']){
                $data['password'] = bcrypt($data['password']);
            }
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            $u = \App\Models\User::find($record->id);
            if ($data['password'] !== null) {
                $u->password = bcrypt($data['password']);
            }
            $u->departemen_id = $data['departemen_id'];
            $u->position_id = $data['position_id'];
            $u->level_id = $data['level_id'];
            $u->nik = $data['nik'];
            $u->name = $data['name'];
            $u->email = $data['email'];
            $u->email_verified_at = $data['email_verified_at'];
            $u->is_suspended = $data['is_suspended'];
            $u->work_location = $data['work_location'];
            $u->saldo_cuti = $data['saldo_cuti'];
            $u->join_at = $data['join_at'];
            $u->loan_limit = $data['loan_limit'];
            $u->total_salary = $data['total_salary'];
            $u->approval_line = $data['approval_line'];
            $u->approval_manager = $data['approval_manager'];
            $u->approval_hr = $data['approval_hr'];
            $u->approval_owner = $data['approval_owner'];
            $u->approval_fat = $data['approval_fat'];
            $u->image = $data['image'];
            $u->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getMessage());
        }
        return $record;
    }
}
