<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceIn;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class Attendance extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                AttendanceIn::query()
            )
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
            ]);
    }
}
