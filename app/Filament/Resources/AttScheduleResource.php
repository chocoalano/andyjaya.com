<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttScheduleResource\Pages;
use App\Http\Spreadsheet\ScheduleExcel;
use App\Models\UserAttGroupSchedule;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AttScheduleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = UserAttGroupSchedule::class;

    protected static ?string $navigationLabel = 'Schedule';
    protected static ?string $navigationIcon = 'fas-calendar';
    protected static ?string $navigationGroup = 'Attendance Settings';

    public static function getGloballySearchableAttributes(): array
    {
        return [
        'team.name',
        'time.type',
        'date_work',
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
                    ->options(\App\Models\UserAttGroup::all()->pluck('name', 'id'))
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->minLength(2)->maxLength(50)->required(),
                        Forms\Components\Select::make('user_id')
                            ->options(\App\Models\User::all()->pluck('name', 'id'))
                            ->label('Leader Team')
                            ->preload()
                            ->searchable()
                            ->required()
                    ])
                    ->required(),
                Forms\Components\Select::make('att_time_id')
                    ->label('Choose Time')
                    ->options(\App\Models\AttTime::all()->pluck('type', 'id'))
                    ->createOptionForm([
                        Forms\Components\TextInput::make('type')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TimePicker::make('in')
                            ->required(),
                        Forms\Components\TimePicker::make('out')
                            ->required(),
                    ])
                    ->required(),
                Forms\Components\Repeater::make('dateset')->schema([
                    Forms\Components\DatePicker::make('date_work')
                        ->required()->columnSpanFull(),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('download')
                ->label('Download Format Import')
                ->icon('fas-file-excel')
                ->url(fn (): string => route('download-format-import', 'schedule-attendance'))
                ->openUrlInNewTab(),
                Tables\Actions\Action::make('import')
                ->label('Import Data')
                ->icon('fas-file-import')
                ->form([
                    Forms\Components\FileUpload::make('fileImport')
                    ->storeFiles(false)
                    ->columnSpanFull()
                    ->required(),
                ])
                ->action(function (array $data): void {
                    $file = $data['fileImport'];
                    $path = $file->getRealPath();
                    $ss = IOFactory::load($path);
                    $sheet = $ss->getActiveSheet();
                    $highestColumnIndex = $sheet->getHighestDataColumn();
                    $headers = $sheet->rangeToArray('A1:' . $highestColumnIndex . '1', null, true, false)[0];
                    $datarow = [];
                    foreach ($sheet->getRowIterator(2) as $row) {
                        $rowData = [];
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false);
                        foreach ($cellIterator as $cell) {
                            $columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($cell->getColumn()) - 1;
                            if((int)$cell->getValue() > 40000){
                                $phpDate = date::excelToDateTimeObject((int)$cell->getValue());
                                $value = $phpDate->format('Y-m-d');
                            }else{
                                $value = $cell->getValue();
                            }
                            $rowData[$headers[$columnIndex]] = $value;
                        }
                        $datarow[] = $rowData;
                    }
                    $u = new ScheduleExcel();
                    $exec = $u->import_from_excel($datarow);
                    if($exec === 'success import!'){
                        Notification::make()
                        ->title('Import successfully')
                        ->success()
                        ->send();
                    }else{
                        Notification::make()
                        ->title('Import Unsuccessfully')
                        ->success()
                        ->body($exec)
                        ->send();
                    }
                }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('time.type'),
                Tables\Columns\TextColumn::make('date_work')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                DateRangeFilter::make('date_work')
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->mutateRecordDataUsing(function (array $data): array {
                    $d = UserAttGroupSchedule::where('user_att_group_id', $data['user_att_group_id'])
                    ->where('att_time_id', $data['att_time_id'])
                    ->get();
                    $date = [];
                    foreach ($d as $k) {
                        array_push($date, ['date_work'=>$k->date_work]);
                    }
                    $data['dateset'] = $date;
                    return $data;
                })
                ->using(function (Model $record, array $data): Model {
                    $model = null;
                    foreach ($data['dateset'] as $k) {
                        $model = UserAttGroupSchedule::updateOrCreate(
                            [
                                'user_att_group_id' => $record->user_att_group_id,
                                'att_time_id' => $record->att_time_id,
                                'date_work' => $k['date_work'],
                            ],
                            [
                            'date_work' => $k['date_work'],
                            'updated_at' => Carbon::now(),
                            ]
                        );
                    }
                    return $model;
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
            'index' => Pages\ManageAttSchedules::route('/'),
        ];
    }
}
