<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttTimeResource\Pages;
use App\Filament\Resources\AttTimeResource\RelationManagers;
use App\Models\AttTime;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttTimeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = AttTime::class;

    protected static ?string $navigationIcon = 'fas-clock';
    protected static ?string $navigationLabel = 'Time Setup';
    protected static ?string $navigationGroup = 'Attendance Settings';

    public static function getGloballySearchableAttributes(): array
    {
        return [
        'type',
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
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TimePicker::make('in')
                    ->required(),
                Forms\Components\TimePicker::make('out')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('in'),
                Tables\Columns\TextColumn::make('out'),
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
            'index' => Pages\ManageAttTimes::route('/'),
        ];
    }
}
