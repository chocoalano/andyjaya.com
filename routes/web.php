<?php

use App\Events\BroadcastEvent;
use App\Http\Controllers\ImportFormatController;
use App\Http\Controllers\Payroll;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/hrd');
});
Route::get('/download-format-import/{id}', [ImportFormatController::class, 'index'])->name('download-format-import');
Route::get('/slip-gaji/{id}', [Payroll::class, 'slip'])->name('slipgaji.pdf');
