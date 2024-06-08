<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\AttArea;
use App\Models\AttendanceIn;
use App\Models\Departement;
use App\Models\Level;
use App\Models\Position;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class AttendanceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = AttendanceIn::class;

    protected static ?string $navigationIcon = 'fas-fingerprint';
    protected static ?string $navigationLabel = 'Attendances';

    public static function getGloballySearchableAttributes(): array
    {
        return [
        'user.name', 'schedule.date_work', 'status'
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
            'delete_any'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make("Presence In")
                    ->columns(['sm' => 1,'xl' => 2,'2xl' => 3])
                    ->schema([
                        Select::make('user_id')
                            ->label('Choose User')
                            ->searchable()
                            ->preload()
                            ->relationship('user', 'name')
                            ->required(),
                        Select::make('area_id')
                            ->label('Choose Area')
                            ->searchable()
                            ->preload()
                            ->options(AttArea::all()->pluck('name', 'id'))
                            ->required(),
                        Select::make('att_group_schedule_id')
                            ->label('Choose Schedule')
                            ->searchable()
                            ->preload()
                            ->relationship('schedule', 'date_work')
                            ->required(),
                        TextInput::make('lat')->numeric()->required(),
                        TextInput::make('lng')->numeric()->required(),
                        TimePicker::make('time')->required(),
                        FileUpload::make('photo')
                            ->image()
                            ->imageEditor()
                            ->directory('attendance-in')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make("Presence Out")
                    ->columns(['sm' => 1,'xl' => 2,'2xl' => 3])
                    ->schema([
                        Select::make('pulang.user_id')
                            ->label('Choose User')
                            ->searchable()
                            ->preload()
                            ->relationship('user', 'name')
                            ->required(),
                        Select::make('pulang.area_id')
                            ->label('Choose Area')
                            ->searchable()
                            ->preload()
                            ->options(AttArea::all()->pluck('name', 'id'))
                            ->required(),
                        Select::make('pulang.att_group_schedule_id')
                            ->label('Choose Schedule')
                            ->searchable()
                            ->preload()
                            ->relationship('schedule', 'date_work')
                            ->required(),
                        TextInput::make('pulang.lat')->numeric()->required(),
                        TextInput::make('pulang.lng')->numeric()->required(),
                        TimePicker::make('pulang.time')->required(),
                        FileUpload::make('pulang.photo')
                            ->image()
                            ->imageEditor()
                            ->directory('attendance-out')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Photo In')
                    ->square(),
                Tables\Columns\ImageColumn::make('pulang.photo')
                    ->label('Photo Out')
                    ->square(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schedule.date_work')
                    ->searchable()
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lat')
                    ->label('Latitude')
                    ->description(fn (AttendanceIn $record): string => !is_null($record->pulang->lat) ? $record->pulang->lat :'Belum Pulang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lng')
                    ->label('Longitude')
                    ->description(fn (AttendanceIn $record): string => !is_null($record->pulang->lng) ? $record->pulang->lng :'Belum Pulang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->description(fn (AttendanceIn $record): string => !is_null($record->pulang->time) ? $record->pulang->time :'Belum Pulang'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->description(fn (AttendanceIn $record): string => !is_null($record->pulang->status) ? $record->pulang->status :'Belum Pulang'),
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
                DateRangeFilter::make('created_at'),
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
                Tables\Actions\EditAction::make()
                ->mutateRecordDataUsing(function (array $data, AttendanceIn $record): array {
                    $data['pulang']=$record->pulang;
                    return $data;
                })->using(function (Model $record, array $data): Model {
                    $record->update($data);
                    $record->pulang->update($data['pulang']);
             
                    return $record;
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
            'index' => Pages\ManageAttendances::route('/'),
        ];
    }
}
