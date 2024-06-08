<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsersResource\Pages;
use App\Filament\Resources\UsersResource\RelationManagers;
use App\Models\Departement;
use App\Models\Level;
use App\Models\Position;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getGloballySearchableAttributes(): array
    {
        return [
        'rolefind.name',
        'name',
        'email',
    ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'lock'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Users Personal Data')
                ->schema([
                    Forms\Components\TextInput::make('nik')->numeric()->minLength(5)->maxLength(20)->required(),
                    Forms\Components\Select::make('roles')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable(),
                    Forms\Components\TextInput::make('name')->minLength(2)->maxLength(50)->required(),
                    Forms\Components\TextInput::make('email')->email()->required(),
                    Forms\Components\DateTimePicker::make('email_verified_at')->required(),
                    Forms\Components\TextInput::make('password')->password()->revealable()->minLength(6)->maxLength(10),
                    Forms\Components\TextInput::make('password_confirmation')->password()->revealable()->minLength(6)->maxLength(10),
                    Forms\Components\Toggle::make('is_suspended')->required(),
                ]),
                Forms\Components\Fieldset::make('Users Employment Data')
                ->schema([
                    Forms\Components\Select::make('work_location')->options([
                        'office'=>'Office',
                        'store'=>'Store',
                        'warehouse'=>'Warehouse',
                    ])->required(),
                    Forms\Components\Select::make('departemen_id')->label('Departement')->relationship(name: 'departement', titleAttribute: 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                    ]),
                    Forms\Components\Select::make('position_id')->label('Position')->relationship(name: 'position', titleAttribute: 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                    ]),
                    Forms\Components\Select::make('level_id')->label('Level')
                    ->relationship(name: 'level', titleAttribute: 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                    ]),
                    Forms\Components\TextInput::make('saldo_cuti')->numeric()->minLength(0)->maxLength(14)->required(),
                    Forms\Components\TextInput::make('loan_limit')->numeric()->minLength(0)->maxLength(14)->required(),
                    Forms\Components\DatePicker::make('join_at')->required(),
                    Forms\Components\TextInput::make('total_salary')->numeric()->minLength(0)->required(),
                    Forms\Components\Select::make('approval_line')->options(User::all()->pluck('name', 'id'))->required(),
                    Forms\Components\Select::make('approval_manager')->options(User::all()->pluck('name', 'id'))->required(),
                    Forms\Components\Select::make('approval_hr')->options(User::all()->pluck('name', 'id'))->required(),
                    Forms\Components\Select::make('approval_owner')->options(User::all()->pluck('name', 'id'))->required(),
                    Forms\Components\Select::make('approval_fat')->options(User::all()->pluck('name', 'id'))->required(),
                    Forms\Components\FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->directory('users-avatar')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\ImageColumn::make('image')->circular(),
            Tables\Columns\TextColumn::make('nik')->searchable(),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('email_verified_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])
        ->filters([
            Tables\Filters\TrashedFilter::make(),
            Tables\Filters\SelectFilter::make('roles')
            ->relationship('rolefind', 'name')
            ->multiple()
            ->preload()
            ->searchable()
            ->options(
                fn (): array => \Spatie\Permission\Models\Role::query()
                ->pluck('name', 'id')
                ->all()
            ),
            Tables\Filters\SelectFilter::make('departement')
            ->relationship('departement', 'name')
            ->multiple()
            ->preload()
            ->searchable()
            ->options(
                fn (): array => Departement::query()
                ->pluck('name', 'id')
                ->all()
            ),
            Tables\Filters\SelectFilter::make('position')
            ->relationship('position', 'name')
            ->multiple()
            ->preload()
            ->searchable()
            ->options(
                fn (): array => Position::query()
                ->pluck('name', 'id')
                ->all()
            ),
            Tables\Filters\SelectFilter::make('level')
            ->relationship('level', 'name')
            ->multiple()
            ->preload()
            ->searchable()
            ->options(
                fn (): array => Level::query()
                ->pluck('name', 'id')
                ->all()
            )
        ])
        ->actions([
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->beforeReplicaSaved(function (User $replica): void {
                        $carbonDate = \Illuminate\Support\Carbon::now();
                        $datetime = $carbonDate->format('YmdHis');
                        $replica->name = "[new]$datetime._$replica->name";
                        $replica->nik = $datetime;
                        $replica->email = "[new]$datetime._$replica->email";
                    })->requiresConfirmation()
                    ->modalHeading('Replicate Data')
                    ->modalDescription('Are you sure you\'d like to replicate this data? This cannot be undone.')
                    ->modalSubmitActionLabel('Yes, replicate it'),
                Tables\Actions\RestoreAction::make(),
            ])
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUsers::route('/create'),
            'edit' => Pages\EditUsers::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationGroup(): ?string
    {
        return \BezhanSalleh\FilamentShield\Support\Utils::isResourceNavigationGroupEnabled()
        ? __('Authorization')
        : '';
    }
}
