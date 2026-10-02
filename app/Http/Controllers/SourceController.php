<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\View\View;

class SourceController extends Controller
{
    public function __invoke(): View
    {
        return view('sources', [
            'topics' => Source::TOPICS,
            'sources' => Source::orderBy('sort_order')->get()->groupBy('topic'),
        ]);
    }
}
