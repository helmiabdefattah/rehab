<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    public function index(): JsonResponse
    {
        $exercises = Exercise::with('tags')->ordered()->get()->map->toClientArray();

        return response()->json(['data' => $exercises]);
    }
}
