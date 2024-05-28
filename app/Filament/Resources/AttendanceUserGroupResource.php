<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceUserGroupResource\Pages;
use App\Filament\Resources\AttendanceUserGroupResource\RelationManagers;
use App\Models\UserAttGroup;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AttendanceUserGroupResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = UserAttGroup::class;

    protected static ?string $navigationIcon = 'fas-object-group';
    protected static ?string $navigationLabel = 'User Team';
    protected static ?string $navigationGroup = 'Attendance Settings';

    public static function getGloballySearchableAttributes(): array
    {
        return [
        'name',
        'userTeams.name',
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
                Forms\Components\Select::make('user_att_group_id')
                    ->label('Choose Team')
                    ->options(UserAttGroup::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Choose Members')
                    ->options(\App\Models\User::all()->pluck('name', 'id'))
                    ->preload()
                    ->multiple()
                    ->searchable()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('userTeams.name')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->using(function (Model $record, array $data): Model {
                    // dd($record, $data);
                    $q = UserAttGroup::find($record->id);
                    $q->userTeams()->attach($data['user_id']);
                    return $q;
                }),
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
            'index' => Pages\ManageAttendanceUserGroups::route('/'),
        ];
    }
}
