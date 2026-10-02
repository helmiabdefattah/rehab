<?php

namespace App\Http\Controllers;

use App\Enums\Activity;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'activities' => Activity::cases(),
            'quick' => config('warmup.quick'),
        ]);
    }
}
