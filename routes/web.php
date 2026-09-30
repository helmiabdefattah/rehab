<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SafetyController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\WarmupController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::prefix('warm-up')->name('warmup.')->controller(WarmupController::class)->group(function () {
    Route::get('/', 'builder')->name('builder');
    Route::get('quick/{minutes}', 'quick')->whereIn('minutes', ['5', '10', '15'])->name('quick');
    Route::get('focus/{area}', 'focus')->whereIn('area', ['knee', 'hip'])->name('focus');
    Route::get('focus/{area}/start/{minutes}', 'startFocus')->whereIn('area', ['knee', 'hip'])->whereIn('minutes', ['5', '10'])->name('focus.start');
});

Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
Route::get('exercises/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');

Route::view('timer', 'timer')->name('timer');
Route::view('progress', 'progress')->name('progress');
Route::view('settings', 'settings')->name('settings');
Route::get('safety', SafetyController::class)->name('safety');
Route::get('sources', SourceController::class)->name('sources');
