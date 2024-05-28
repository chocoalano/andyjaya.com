<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Filament\Resources\PayrollResource\RelationManagers;
use App\Models\AttendanceIn;
use App\Models\Payroll;
use App\Models\User;
use App\Models\UserAttGroup;
use App\Models\UserAttGroupSchedule;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
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
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, ?int $state) {
                            $userGroup = UserAttGroup::whereHas('userTeams', function ($query) use ($state) {
                                $query->where('user_id', $state);
                            })->first();
                            if($userGroup){
                                $total_schedule = UserAttGroupSchedule::where('user_att_group_id', $userGroup->id)
                                ->whereYear('date_work', date('Y'))
                                ->whereMonth('date_work', date('m'))
                                ->count('*');

                                $total_present = AttendanceIn::where('user_id', $state)
                                ->whereYear('created_at', date('Y'))
                                ->whereMonth('created_at', date('m'))
                                ->whereHas('pulang', function ($query) {
                                    $query
                                    ->whereYear('created_at', date('Y'))
                                    ->whereMonth('created_at', date('m'));
                                })
                                ->count('*');

                                $total_late = AttendanceIn::where('user_id', $state)
                                ->where('status', 'late')
                                ->whereYear('created_at', date('Y'))
                                ->whereMonth('created_at', date('m'))
                                ->whereHas('pulang', function ($query) {
                                    $query
                                    ->whereYear('created_at', date('Y'))
                                    ->whereMonth('created_at', date('m'));
                                })
                                ->count('*');

                                $total_unlate = AttendanceIn::where('user_id', $state)
                                ->where('status', 'unlate')
                                ->whereYear('created_at', date('Y'))
                                ->whereMonth('created_at', date('m'))
                                ->whereHas('pulang', function ($query) {
                                    $query
                                    ->whereYear('created_at', date('Y'))
                                    ->whereMonth('created_at', date('m'));
                                })
                                ->count('*');

                                $total_early = AttendanceIn::where('user_id', $state)
                                ->where('status', 'early')
                                ->whereYear('created_at', date('Y'))
                                ->whereMonth('created_at', date('m'))
                                ->whereHas('pulang', function ($query) {
                                    $query
                                    ->whereYear('created_at', date('Y'))
                                    ->whereMonth('created_at', date('m'));
                                })
                                ->count('*');

                                $user = User::find($state);

                                $subtotal_payroll = round(((float)$user->total_salary / (int)$total_schedule)*(int)$total_present, 2);

                                $set('total_schedule', $total_schedule);
                                $set('total_present', $total_present);
                                $set('total_late', $total_late);
                                $set('total_unlate', $total_unlate);
                                $set('total_early', $total_early);
                                $set('subtotal_payroll', $subtotal_payroll);
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
                    Forms\Components\TextInput::make('subtotal_payroll')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('total_payroll')
                        ->disabled()
                        ->numeric(),
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
                            Forms\Components\TextInput::make('amount')->numeric()->minLength(0)->required(),
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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_schedule')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_present')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_late')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_unlate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_early')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal_payroll')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_payroll')
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
