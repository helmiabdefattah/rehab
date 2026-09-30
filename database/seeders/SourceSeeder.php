<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = require database_path('data/sources.php');

        foreach ($sources as $index => $source) {
            Source::updateOrCreate(['key' => $source['key']], [...$source, 'sort_order' => $index + 1]);
        }

        Source::whereNotIn('key', array_column($sources, 'key'))->delete();
    }
}
