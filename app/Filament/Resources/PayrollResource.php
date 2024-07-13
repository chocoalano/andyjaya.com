<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Filament\Resources\PayrollResource\RelationManagers;
use App\Models\AttendanceIn;
use App\Models\FormPaidLeave;
use App\Models\MoneyLoan;
use App\Models\Payroll;
use App\Models\PermissionForm;
use App\Models\User;
use App\Models\UserAttGroup;
use App\Models\UserAttGroupSchedule;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use DateTime;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PayrollResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'fas-wallet';

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
                Forms\Components\Fieldset::make('Calculate Basic Payroll')
                ->schema([
                    DateRangePicker::make('periode')->separator(' - ')->required(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set, ?int $state) {
                            $periode = $get('periode');
                            if ($periode) {
                                $userGroup = UserAttGroup::whereHas('userTeams', function ($query) use ($state) {
                                    $query->where('user_id', $state);
                                })->first();
                                $dateArray = explode(" - ", $periode);
                                $date1 = DateTime::createFromFormat('d/m/Y', $dateArray[0])->format('Y-m-d');
                                $date2 = DateTime::createFromFormat('d/m/Y', $dateArray[1])->format('Y-m-d');
                                $dateArrayResult = array(
                                    'date1' => $date1,
                                    'date2' => $date2
                                );
                                if($userGroup){
                                    $total_schedule = UserAttGroupSchedule::where('user_att_group_id', $userGroup->id)
                                    ->where('date_work', '>=', $dateArrayResult['date1'])
                                    ->where('date_work', '<=', $dateArrayResult['date2'])
                                    ->count('*');
                                    $date = new DateTime('2024-07-01');
                                    $year = $date->format('Y');
                                    $month = $date->format('m');
                                    $total_present = DB::table('attendances_in as ai')
                                    ->join('attendances_out as ao', 'ai.id', '=', 'ao.att_id')
                                    ->join('att_group_schedule as ags', 'ai.att_group_schedule_id', '=', 'ags.id')
                                    ->whereYear('ags.date_work', '2024')
                                    ->whereMonth('ags.date_work', '07')
                                    ->select('ai.*', 'ao.*', 'ags.*')
                                    ->count('*');

                                    if($total_schedule <= $total_present){
                                        Notification::make()
                                        ->title("There is an anomaly in the data, the absence schedule data must not have a total number less than the number of attendance. If this happens, then recheck the absence schedule data created with the saved absence data and make sure there are no data errors! total schedule: $total_schedule||total presence: $total_present")
                                        ->danger()
                                        ->send();
                                    }else{
                                        $total_late = AttendanceIn::where('user_id', $state)
                                        ->where('status', 'late')
                                        ->whereYear('created_at', $year)
                                        ->whereMonth('created_at', $month)
                                        ->whereHas('pulang', function ($query) use ($year, $month){
                                            $query
                                            ->whereYear('created_at', $year)
                                            ->whereMonth('created_at', $month);
                                        })
                                        ->count('*');

                                        $total_unlate = AttendanceIn::where('user_id', $state)
                                        ->where('status', 'unlate')
                                        ->whereYear('created_at', $year)
                                        ->whereMonth('created_at', $month)
                                        ->whereHas('pulang', function ($query) use ($year, $month) {
                                            $query
                                            ->whereYear('created_at', $year)
                                            ->whereMonth('created_at', $month);
                                        })
                                        ->count('*');

                                        $total_early = AttendanceIn::where('user_id', $state)
                                        ->where('status', 'early')
                                        ->whereYear('created_at', $year)
                                        ->whereMonth('created_at', $month)
                                        ->whereHas('pulang', function ($query) use ($year, $month) {
                                            $query
                                            ->whereYear('created_at', $year)
                                            ->whereMonth('created_at', $month);
                                        })
                                        ->count('*');

                                        $user = User::find($state);
                                        $rp = $total_schedule === 0 ? $total_schedule / (float)$user->total_salary : (float)$user->total_salary / $total_schedule;
                                        $subtotal_payroll = round($rp * $total_present, 2);
                                        
                                        $cekPinjaman = MoneyLoan::where('user_id', $user->id)->sum('total_loan');
                                        $cekCuti = FormPaidLeave::where('user_id', $user->id)
                                        ->whereYear('from_date', $year)
                                        ->whereMonth('from_date', $month)
                                        ->count('*');
                                        $cekIzin = PermissionForm::selectRaw("
                                            SUM(CASE WHEN request_type = 'present-late' THEN 1 ELSE 0 END) AS jumlah_izin_datang_telat,
                                            SUM(CASE WHEN request_type = 'sick' THEN 1 ELSE 0 END) AS jumlah_izin_sakit,
                                            SUM(CASE WHEN request_type = 'not-present' THEN 1 ELSE 0 END) AS jumlah_tidak_masuk_kerja
                                        ")
                                        ->where('user_id', $user->id)
                                        ->where('status_hr', 'approved')
                                        ->whereYear('from_date', $year)
                                        ->whereMonth('from_date', $month)
                                        ->first();
                                        $set('total_schedule', $total_schedule);
                                        $set('total_present', $total_present);
                                        $set('total_late', $total_late);
                                        $set('total_unlate', $total_unlate);
                                        $set('total_early', $total_early);
                                        $set('subtotal_payroll', $subtotal_payroll);
                                        $component = [];
                                        if($cekPinjaman > 0){
                                            array_push($component, [
                                                'title'=>'loan',
                                                'operator'=>'minus',
                                                'amount'=>$cekPinjaman
                                            ]);
                                        }
                                        if((int)$cekIzin->jumlah_izin_datang_telat > 0){
                                            array_push($component, [
                                                'title'=>'present-late',
                                                'operator'=>'minus',
                                                'amount'=>0
                                                // 'amount'=>(int)$cekIzin->jumlah_izin_datang_telat
                                            ]);
                                        }
                                        if((int)$cekIzin->jumlah_izin_sakit > 0){
                                            array_push($component, [
                                                'title'=>'sick',
                                                'operator'=>'minus',
                                                'amount'=>0
                                                // 'amount'=>(int)$cekIzin->jumlah_izin_sakit
                                            ]);
                                        }
                                        if((int)$cekIzin->jumlah_tidak_masuk_kerja > 0){
                                            array_push($component, [
                                                'title'=>'not-present',
                                                'operator'=>'minus',
                                                'amount'=>0
                                                // 'amount'=>(int)$cekIzin->jumlah_tidak_masuk_kerja
                                            ]);
                                        }
                                        if((int)$cekCuti > 0){
                                            array_push($component, [
                                                'title'=>'cuti',
                                                'operator'=>'minus',
                                                'amount'=>0
                                                // 'amount'=>(int)$cekIzin->jumlah_tidak_masuk_kerja
                                            ]);
                                        }
                                        $set('components', $component);
                                    }
                                }
                            }else{
                                $set('user_id', null);
                                Notification::make()
                                ->title('Select the period first, after that you select the user!')
                                ->danger()
                                ->send();
                            }
                        })
                        ->required(),
                    Forms\Components\TextInput::make('total_schedule')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('total_present')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('total_late')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('total_unlate')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('total_early')
                        ->required()
                        ->numeric(),
                    TextInput::make('subtotal_payroll')
                        ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2)
                        ->required(),
                    TextInput::make('total_payroll')
                        ->disabled()
                        ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2),
                ]),
                Forms\Components\Fieldset::make('Calculate Components Payroll')
                ->schema([
                    Forms\Components\Repeater::make('components')
                        ->schema([
                            Forms\Components\TextInput::make('title')->required(),
                            Forms\Components\Select::make('operator')
                                ->options([
                                    'plus' => 'Plus (+)',
                                    'minus' => 'Minus (-)',
                                    'devide' => 'Devide (:)',
                                    'times' => 'Times (x)',
                                ])
                                ->required(),
                            TextInput::make('amount')
                                ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2)
                                ->required(),
                        ])
                        ->columns(3)
                        ->columnSpanFull()
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_schedule')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_present')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_late')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_unlate')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_early')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal_payroll')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_payroll')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_periode')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('end_periode')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $q = Payroll::with('component')
                        ->where('id', $data['id'])
                        ->first();
                        $data['user_id'] = $q->user_id;
                        $data['total_schedule'] = $q->total_schedule;
                        $data['total_present'] = $q->total_present;
                        $data['total_late'] = $q->total_late;
                        $data['total_unlate'] = $q->total_unlate;
                        $data['total_early'] = $q->total_early;
                        $data['subtotal_payroll'] = $q->subtotal_payroll;
                        $data['total_payroll'] = $q->total_payroll;
                        $data['components'] = [];
                        foreach ($q['component'] as $key) {
                            array_push($data['components'], [
                                'title' => $key->title,
                                'operator' => $key->operator,
                                'amount' => $key->amount,
                            ]);
                        }
                        return $data;
                    }),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('Salary slip')
                    ->icon('fas-download')
                    ->url(fn (Payroll $payroll): string => route('slipgaji.pdf', $payroll->id))
                    ->openUrlInNewTab(),
                ])
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
            'index' => Pages\ManagePayrolls::route('/'),
        ];
    }
}
