<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateRoutineRequest;
use App\Services\Warmup\RoutineBuilder;
use Illuminate\Http\JsonResponse;

class RoutineController extends Controller
{
    public function store(GenerateRoutineRequest $request, RoutineBuilder $builder): JsonResponse
    {
        return response()->json(['data' => $builder->build($request->toRoutineRequest())]);
    }
}
