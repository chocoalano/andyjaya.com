<?php

namespace App\Filament\Widgets;

use App\Models\Departement;
use App\Models\Level;
use App\Models\PermissionForm;
use App\Models\Position;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\CarbonPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsHrdOverview extends BaseWidget
{
    use HasPageShield;
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $dept = $this->parsingData(Departement::class);
        $pst = $this->parsingData(Position::class);
        $lvl = $this->parsingData(Level::class);
        $pf = $this->parsingData(PermissionForm::class);
        return [
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
