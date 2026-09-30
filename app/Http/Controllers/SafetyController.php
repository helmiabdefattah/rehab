<?php

namespace App\Http\Controllers;

use App\Enums\Level;
use App\Enums\Stage;
use Illuminate\View\View;

class SafetyController extends Controller
{
    public function __invoke(): View
    {
        return view('safety', [
            'levels' => Level::cases(),
            'stages' => Stage::cases(),
        ]);
    }
}
