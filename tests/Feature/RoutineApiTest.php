<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineApiTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_generates_a_personalised_routine(): void
    {
        $response = $this->postJson('/api/routines', [
            'activity' => 'football',
            'minutes' => 15,
            'intensity' => 'moderate',
            'level' => 2,
            'equipment' => ['mini-band', 'ball'],
            'readiness' => ['checked' => true, 'knee_pain' => false, 'knee_swelling' => false, 'score' => 8],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Football | 15 min | Moderate | Good readiness')
            ->assertJsonPath('data.total_seconds', 900)
            ->assertJsonPath('data.readiness.status', 'ready')
            ->assertJsonStructure(['data' => ['items' => [['slug', 'name', 'seconds', 'stage', 'exercise' => ['instructions', 'video' => ['embed', 'search']]]], 'stages']]);
    }

    public function test_caution_answers_adjust_the_routine(): void
    {
        $this->postJson('/api/routines', [
            'activity' => 'running',
            'minutes' => 10,
            'intensity' => 'high',
            'level' => 3,
            'readiness' => ['checked' => true, 'instability' => true, 'score' => 7],
        ])
            ->assertOk()
            ->assertJsonPath('data.readiness.status', 'caution')
            ->assertJsonPath('data.level', 1)
            ->assertJsonPath('data.intensity', 'light');
    }

    public function test_validates_input(): void
    {
        $this->postJson('/api/routines', ['activity' => 'swimming', 'minutes' => 7, 'level' => 9, 'equipment' => ['rocket']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['activity', 'minutes', 'intensity', 'level', 'equipment.0']);
    }

    public function test_lists_exercises(): void
    {
        $this->getJson('/api/exercises')->assertOk()->assertJsonCount(40, 'data');
    }
}
