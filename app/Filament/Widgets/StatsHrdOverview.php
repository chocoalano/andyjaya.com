<?php

namespace App\Filament\Widgets;

use App\Models\Departement;
use App\Models\Level;
use App\Models\PermissionForm;
use App\Models\Position;
use App\Models\User;
use App\Models\UserAttGroupSchedule;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\CarbonPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsHrdOverview extends BaseWidget
{
    use HasWidgetShield;
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $dept = $this->parsingData(Departement::class);
        $pst = $this->parsingData(Position::class);
        $lvl = $this->parsingData(Level::class);
        $pf = $this->parsingData(PermissionForm::class);
        
        $next_day = date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day'));
        $pay = User::where('join_at', $next_day)->count();
        $start = Carbon::now()->firstOfMonth();
        $end = Carbon::now()->lastOfMonth();
        $sch = UserAttGroupSchedule::where(function($q)use($start, $end){
            $q
            ->where('date_work', '>=', $start->toDateString())
            ->where('date_work', '<=', $end->toDateString());
        })->count();

        return [
            Stat::make('Total employees paid tomorrow', $pay.' Total payroll')
                ->description(date("d F Y", strtotime($next_day)).' Payroll Employees')
                ->color('warning'),
            Stat::make('Total employees schedule', $sch.' Total schedules')
                ->description(date("d F Y", strtotime($start)).' Sampai dengan '.date("d F Y", strtotime($end)))
                ->color('error'),
            Stat::make('Job Departement total', $dept['count'])
                ->description($dept['count'].' increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($dept['toArray'])
                ->color('success'),
            Stat::make('Job Position total', $pst['count'])
                ->description($pst['count'].' increase')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart($pst['toArray'])
                ->color('danger'),
            Stat::make('Job Level total', $lvl['count'])
                ->description($lvl['count'].' increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($lvl['toArray'])
                ->color('warning'),
            Stat::make('Permission Request', $pf['count'])
                ->description($pf['count'].' increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($pf['toArray'])
                ->color('primary'),
        ];
    }

    public function parsingData($class)
    {
        $start = Carbon::parse($class::min("created_at"));
        $end = Carbon::now();
        $period = CarbonPeriod::create($start, "1 month", $end);
        $usersPerMonth = collect($period)->map(function ($date) use ($class) {
            $endDate = $date->copy()->endOfMonth();
            return [
                "count" => $class::where("created_at", "<=", $endDate)->count(),
            ];
        });
        $count=$class::count();
        $usersPerMonth->pluck("count")->toArray();
        return [
            "count"=> $count,
            "toArray"=> $usersPerMonth->pluck("count")->toArray(),
        ];
    }
}
