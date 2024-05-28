<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionFormResource\Pages;
use App\Models\PermissionForm;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PermissionFormResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PermissionForm::class;

    protected static ?string $navigationIcon = 'fas-file-invoice';
    protected static ?string $navigationGroup = 'Permission Forms';
    protected static ?int $navigationSort = 1;

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
                Forms\Components\Select::make('request_type')
                    ->options([
                        'present-late'=>'Present Late',
                        'sick'=>'Sick',
                        'not-present'=>'Not Present',
                        'personal-permission'=>'Personal Permission',
                        'other'=>'Other'
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('from_date')
                    ->required(),
                Forms\Components\DatePicker::make('to_date')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('file')
                    ->columnSpanFull()
                    ->image()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('request_type'),
                Tables\Columns\TextColumn::make('from_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_hr')
                ->label('Status')
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
                Tables\Filters\SelectFilter::make('status_hr')
                ->options([
                    'waiting' => 'Waiting',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected'
                ])
                ->multiple()
                ->preload()
                ->searchable(),
                DateRangeFilter::make('created_at')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
                    ->modalHeading('Approved permission')
                    ->modalSubmitActionLabel('Submit')
                    ->action(function (PermissionForm $record, array $data): void {
                        $record->status_hr = $data['status_hr'];
                        $record->save();
                        $user = User::find($record->user_id);
                        Notification::make()
                            ->title('Approved successfully')
                            ->broadcast($user)
                            ->sendToDatabase($user);
                    })
                    ->hidden(fn (PermissionForm $record): bool => $record->user->approval_hr !== auth()->user()->id || $record->status_hr !=='waiting' ? true : false),
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
            'index' => Pages\ManagePermissionForms::route('/'),
        ];
    }
}
