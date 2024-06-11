<?php

namespace App\Filament\Resources\MoneyLoanResource\Pages;

use App\Filament\Resources\MoneyLoanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMoneyLoans extends ManageRecords
{
    protected static string $resource = MoneyLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data): array {
                $data['user_id'] = auth()->id();
                $data['status'] = 'unpaid';
                return $data;
            }),
        ];
    }
}
