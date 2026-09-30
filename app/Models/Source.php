<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    public const TOPICS = [
        'warm-up' => 'Warm-up principles',
        'sport' => 'Sports preparation',
        'technique' => 'Exercise technique & muscle activation',
        'injury-aware' => 'Injury-aware exercise considerations',
    ];
}
