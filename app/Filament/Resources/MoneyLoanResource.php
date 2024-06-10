<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MoneyLoanResource\Pages;
use App\Filament\Resources\MoneyLoanResource\RelationManagers;
use App\Models\MoneyLoan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class MoneyLoanResource extends Resource
{
    protected static ?string $model = MoneyLoan::class;

    protected static ?string $navigationIcon = 'fas-coins';
    protected static ?string $navigationGroup = 'Loans';
    public static function getGloballySearchableAttributes(): array
    {
        return [
        'user.name', 'total_loan'
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Choose User')
                    ->searchable()
                    ->preload()
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('total_loan')
                    ->required()
                    ->minValue(5000)
                    ->maxValue(500000)
                    ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2)
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull()
                    ->required()
                    ->autosize()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_hr')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_loan')
                    ->searchable(),
            ])
            ->filters([
                DateRangeFilter::make('created_at'),
            ])
            ->actions([
                Tables\Actions\Action::make('Approved')
                    ->icon('fas-signature')
                    ->form([
                        Forms\Components\Radio::make('status_hr')
                        ->label('Status Approved')
                        ->options([
                            'waiting' => 'Waiting',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected'
                        ]),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Approved loan')
                    ->modalSubmitActionLabel('Submit')
                    ->action(function (MoneyLoan $record, array $data): void {
                        $record->status_hr = $data['status_hr'];
                        $record->save();
                        $user = User::find($record->user_id);
                        Notification::make()
                            ->title('Approved successfully')
                            ->broadcast($user)
                            ->sendToDatabase($user);
                    })
                    ->hidden(fn (MoneyLoan $record): bool => $record->user->approval_hr !== auth()->user()->id || $record->status_hr !=='waiting' ? true : false),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMoneyLoans::route('/'),
        ];
    }
}
