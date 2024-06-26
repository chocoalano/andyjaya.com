<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceIn;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AttendanceUnlateChart extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Attendance';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $start = Carbon::parse(AttendanceIn::min("created_at"));
        $end = Carbon::now();
        $period = CarbonPeriod::create($start, "1 month", $end);

        $usersPerMonth = collect($period)->map(function ($date) {
            $endDate = $date->copy()->endOfMonth();

            return [
                "count" => AttendanceIn::where("created_at", "<=", $endDate)->where('status', 'unlate')->count(),
                "month" => $endDate->format("Y-m-d")
            ];
        });

        $data = $usersPerMonth->pluck("count")->toArray();
        $labels = $usersPerMonth->pluck("month")->toArray();
 
        return [
            'datasets' => [
                [
                    'label' => 'Attendance unlate',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
