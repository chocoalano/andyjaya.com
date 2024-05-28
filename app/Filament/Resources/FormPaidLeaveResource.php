<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormPaidLeaveResource\Pages;
use App\Models\FormPaidLeave;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class FormPaidLeaveResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = FormPaidLeave::class;

    protected static ?string $navigationIcon = 'fas-plane';
    protected static ?string $navigationGroup = 'Permission Forms';
    public static function getGloballySearchableAttributes(): array
    {
        return [
        'user.name',
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'lock'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('from_date')
                    ->required(),
                Forms\Components\DatePicker::make('to_date')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_line')
                ->label('Approved Line')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'waiting' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                }),
                Tables\Columns\TextColumn::make('status_mngr')
                ->label('Approved Manager')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'waiting' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                }),
                Tables\Columns\TextColumn::make('status_hr')
                ->label('Approved HRD')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'waiting' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('Approved_hrd')
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
                    ->modalHeading('Approved permission')
                    ->modalSubmitActionLabel('Submit')
                    ->action(function (FormPaidLeave $record, array $data): void {
                        $record->status_hr = $data['status_hr'];
                        $record->save();
                        $user = User::find($record->user_id);
                        $notify = [$user->approval_line, $user->approval_manager, $user->approval_hr];
                        $notifyTo = User::whereIn('id', $notify)->get();
                        Notification::make()
                                    ->title('Paid leave hrd approved saved')
                                    ->success()
                                    ->broadcast($notifyTo)
                                    ->sendToDatabase($notifyTo);
                    })
                    ->hidden(fn (FormPaidLeave $record): bool => ($record->user->approval_hr !== auth()->user()->id || $record->status_hr !=='waiting') ? true : false),
                Tables\Actions\Action::make('Approved_line')
                    ->icon('fas-signature')
                    ->form([
                        Forms\Components\Radio::make('status_line')
                        ->label('Status Approved')
                        ->options([
                            'waiting' => 'Waiting',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected'
                        ]),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Approved permission')
                    ->modalSubmitActionLabel('Submit')
                    ->action(function (FormPaidLeave $record, array $data): void {
                        $record->status_line = $data['status_line'];
                        $record->save();
                        $user = User::find($record->user_id);
                        $notify = [$user->approval_line, $user->approval_manager, $user->approval_hr];
                        $notifyTo = User::whereIn('id', $notify)->get();
                        Notification::make()
                                    ->title('Paid leave line approved saved')
                                    ->success()
                                    ->broadcast($notifyTo)
                                    ->sendToDatabase($notifyTo);
                    })
                    ->hidden(fn (FormPaidLeave $record): bool => ($record->user->approval_line !== auth()->user()->id || $record->status_line !=='waiting') ? true : false),
                Tables\Actions\Action::make('Approved_manager')
                    ->icon('fas-signature')
                    ->form([
                        Forms\Components\Radio::make('status_mngr')
                        ->label('Status Approved')
                        ->options([
                            'waiting' => 'Waiting',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected'
                        ]),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Approved permission')
                    ->modalSubmitActionLabel('Submit')
                    ->action(function (FormPaidLeave $record, array $data): void {
                        $record->status_mngr = $data['status_mngr'];
                        $record->save();
                        $user = User::find($record->user_id);
                        $notify = [$user->approval_line, $user->approval_manager, $user->approval_hr];
                        $notifyTo = User::whereIn('id', $notify)->get();
                        Notification::make()
                                    ->title('Paid leave manager approved saved')
                                    ->success()
                                    ->broadcast($notifyTo)
                                    ->sendToDatabase($notifyTo);
                    })
                    ->hidden(fn (FormPaidLeave $record): bool => ($record->user->approval_manager !== auth()->user()->id || $record->status_mngr !=='waiting') ? true : false),
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
            'index' => Pages\ManageFormPaidLeaves::route('/'),
        ];
    }
}
