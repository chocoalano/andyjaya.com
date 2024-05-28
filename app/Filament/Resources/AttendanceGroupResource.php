<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceGroupResource\Pages;
use App\Models\UserAttGroup;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceGroupResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = UserAttGroup::class;
    protected static ?string $navigationLabel = 'Teams';
    protected static ?string $navigationIcon = 'fas-layer-group';
    protected static ?string $navigationGroup = 'Attendance Settings';

    public static function getGloballySearchableAttributes(): array
    {
        return [
        'name',
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
            'delete',
            'delete_any',
            'lock'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->minLength(2)->maxLength(50)->required(),
                Forms\Components\Select::make('user_id')
                    ->options(\App\Models\User::all()->pluck('name', 'id'))
                    ->label('Leader Team')
                    ->preload()
                    ->searchable()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('leader.name')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ManageAttendanceGroups::route('/'),
        ];
    }
}
