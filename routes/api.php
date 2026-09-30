<?php

use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\RoutineController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->middleware('throttle:60,1')->group(function () {
    Route::post('routines', [RoutineController::class, 'store'])->name('routines.store');
    Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');
});
