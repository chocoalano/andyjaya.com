<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use DateTime;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ManagePayrolls extends ManageRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->using(function (array $data, string $model): Model {
                $dateArray = explode(" - ", $data['periode']);
                $start = DateTime::createFromFormat('d/m/Y', $dateArray[0])->format('Y-m-d');
                $end = DateTime::createFromFormat('d/m/Y', $dateArray[1])->format('Y-m-d');
                try {
                    DB::beginTransaction();
                    $total = $data['subtotal_payroll'];
                    foreach ($data['components'] as $key) {
                        if ($key['operator'] === 'plus') {
                            $total += $key['amount'];
                        }elseif ($key['operator'] === 'minus') {
                            $total -= $key['amount'];
                        }elseif ($key['operator'] === 'devide') {
                            $total = round($total / $key['amount'], 2);
                        }elseif ($key['operator'] === 'times') {
                            $total = round($total * $key['amount'], 2);
                        }
                    }
                    $q = new Payroll();
                    $q->user_id = $data['user_id'];
                    $q->start_periode = $start;
                    $q->end_periode = $end;
                    $q->total_schedule = $data['total_schedule'];
                    $q->total_present = $data['total_present'];
                    $q->total_late = $data['total_late'];
                    $q->total_unlate = $data['total_unlate'];
                    $q->total_early = $data['total_early'];
                    $q->subtotal_payroll = $data['subtotal_payroll'];
                    $q->total_payroll = $total;
                    $q->save();
                    foreach ($data['components'] as $k) {
                        $p = new PayrollComponent();
                        $p->title = $k['title'];
                        $p->operator = $k['operator'];
                        $p->amount = $k['amount'];
                        $q->component()->save($p);
                    }
                    DB::commit();
                    return $q;
                } catch (\Exception $e) {
                    // If an exception occurs, rollback the transaction
                    DB::rollback();
                    dd($e->getMessage());
                }
            }),
        ];
    }
}
