<?php

use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SafetyController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\WarmupController;
use App\Http\Controllers\WorkoutController;
use Illuminate\Support\Facades\Route;

$splits = ['push', 'pull', 'legs', 'cardio-core'];

Route::get('/', HomeController::class)->name('home');

// Split hub: warm-up + workout for the chosen training split.
Route::get('train/{split}', WorkoutController::class)->whereIn('split', $splits)->name('train');

Route::prefix('warm-up')->name('warmup.')->controller(WarmupController::class)->group(function () {
    Route::get('/', 'builder')->name('builder');
    Route::get('quick/{minutes}', 'quick')->whereIn('minutes', ['5', '10', '15'])->name('quick');
});

Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
Route::get('exercises/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');

Route::view('timer', 'timer')->name('timer');
Route::view('progress', 'progress')->name('progress');
Route::view('settings', 'settings')->name('settings');
Route::get('safety', SafetyController::class)->name('safety');
Route::get('sources', SourceController::class)->name('sources');
