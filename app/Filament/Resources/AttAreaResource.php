<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttAreaResource\Pages;
use App\Filament\Resources\AttAreaResource\RelationManagers;
use App\Models\AttArea;
use ArberMustafa\FilamentLocationPickrField\Forms\Components\LocationPickr;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttAreaResource extends Resource
{
    protected static ?string $model = AttArea::class;
    protected static ?string $navigationIcon = 'fas-location-pin';
    protected static ?string $navigationLabel = 'Attendance Areas';
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
                Fieldset::make('Area')
                ->schema([
                    LocationPickr::make('location')
                        ->defaultLocation([-6.2327155, 106.5212952,15])
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('radius')
                        ->required()
                        ->numeric(),
                    Forms\Components\Textarea::make('address')
                        ->required()
                        ->columnSpanFull(),
                ]),
                Fieldset::make('Area')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Member Users')
                        ->relationship('user', 'name')
                        ->preload()
                        ->multiple()
                        ->columnSpanFull()
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lat')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lng')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('radius')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ManageAttAreas::route('/'),
        ];
    }
}
