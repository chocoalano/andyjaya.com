<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\AttendanceIn;
use App\Models\AttendanceOut;
use ArberMustafa\FilamentLocationPickrField\Forms\Components\LocationPickr;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use DateTime;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->square(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('schedule.date_work')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lat')
                    ->description(fn (AttendanceIn $record): string => $record->pulang ? $record->pulang->lat :'Belum Pulang')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lng')
                    ->description(fn (AttendanceIn $record): string => $record->pulang ? $record->pulang->lng :'Belum Pulang')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->description(fn (AttendanceIn $record): string => $record->pulang ? $record->pulang->time :'Belum Pulang'),
                Tables\Columns\TextColumn::make('status')
                    ->description(fn (AttendanceIn $record): string => $record->pulang ? $record->pulang->status :'Belum Pulang')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unlate' => 'warning',
                        'early' => 'success',
                        'late' => 'danger',
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->label("Attendance Out")
                ->icon("fas-fingerprint")
                ->mutateRecordDataUsing(function (array $data): array {
                    $jam_sekarang = Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                    $data['time'] = $jam_sekarang;
                    $data['lat'] = null;
                    $data['lng'] = null;
                    $data['location'] = null;
                    $data['photo'] = null;
                    return $data;
                })
                ->form([
                    Section::make("Attendance Out")
                    ->columns([
                        'sm' => 1,
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->schema([
                        Select::make('user_id')
                            ->label('Choose User')
                            ->searchable()
                            ->preload()
                            ->relationship('user', 'name')
                            ->required(),
                        Select::make('att_group_schedule_id')
                            ->label('Choose Schedule')
                            ->searchable()
                            ->preload()
                            ->relationship('schedule', 'date_work')
                            ->required(),
                        LocationPickr::make('location')
                        ->columnSpanFull(),
                        TimePicker::make('time')
                            ->disabled()
                            ->columnSpanFull()
                            ->required(),
                        FileUpload::make('photo')
                            ->image()
                            ->imageEditor()
                            ->directory('attendance-out')
                            ->required()
                            ->columnSpanFull(),
                    ])
                ])
                ->using(function (Model $record, array $data): Model {
                    $jam_sekarang = Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                    $c = DB::table('att_group_schedule as ags')
                    ->join('att_times as at','ags.att_time_id','=','at.id')
                    ->where('ags.id','=', $data['att_group_schedule_id'])
                    ->select('at.out')
                    ->first();
                    $waktu_masuk_seharusnya = new DateTime($c->out);
                    $waktu_masuk_sebenarnya = new DateTime($jam_sekarang);
                    $selisih = $waktu_masuk_sebenarnya->diff($waktu_masuk_seharusnya);
                    if ($waktu_masuk_sebenarnya > $waktu_masuk_seharusnya) {
                        $status = 'late';
                    }elseif($waktu_masuk_sebenarnya < $waktu_masuk_seharusnya){
                        $status = 'early';
                    }else{
                        $status = 'unlate';
                    }
                    $q = AttendanceIn::find($record->id);

                    $q->pulang()->create([
                        'user_id' => $data['user_id'],
                        'att_group_schedule_id' => $data['att_group_schedule_id'],
                        'location' => $data['location'],
                        'time' => $jam_sekarang,
                        'difference' => $selisih->format('%H:%I:%S'),
                        'photo' => $data['photo'],
                        'status' => $status,
                    ]);
             
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
