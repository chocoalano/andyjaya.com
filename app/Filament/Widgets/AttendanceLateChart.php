<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceIn;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AttendanceLateChart extends ChartWidget
{
    protected static ?string $heading = 'Attendance';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $start = Carbon::parse(AttendanceIn::min("created_at"));
        $end = Carbon::now();
        $period = CarbonPeriod::create($start, "1 month", $end);

        $usersPerMonth = collect($period)->map(function ($date) {
            $endDate = $date->copy()->endOfMonth();

            return [
                "count" => AttendanceIn::where("created_at", "<=", $endDate)->where('status', 'late')->count(),
                "month" => $endDate->format("Y-m-d")
            ];
        });

        $data = $usersPerMonth->pluck("count")->toArray();
        $labels = $usersPerMonth->pluck("month")->toArray();
 
        return [
            'datasets' => [
                [
                    'label' => 'Attendance in',
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
