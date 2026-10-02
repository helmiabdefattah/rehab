<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineApiTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_generates_a_split_specific_warm_up(): void
    {
        $response = $this->postJson('/api/routines', [
            'activity' => 'legs',
            'minutes' => 15,
            'intensity' => 'moderate',
            'level' => 2,
            'equipment' => ['mini-band', 'step'],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.total_seconds', 900)
            ->assertJsonPath('data.program', 'legs')
            ->assertJsonPath('data.activity', 'legs')
            ->assertJsonStructure(['data' => ['items' => [['slug', 'name', 'seconds', 'stage', 'exercise' => ['instructions', 'animation' => ['pattern']]]], 'stages']]);
    }

    public function test_each_split_generates_a_full_length_routine(): void
    {
        foreach (['push', 'pull', 'legs', 'cardio-core'] as $split) {
            $this->postJson('/api/routines', [
                'activity' => $split,
                'minutes' => 10,
                'intensity' => 'moderate',
                'level' => 2,
            ])->assertOk()->assertJsonPath('data.total_seconds', 600);
        }
    }

    public function test_validates_input(): void
    {
        $this->postJson('/api/routines', ['activity' => 'swimming', 'minutes' => 7, 'level' => 9, 'equipment' => ['rocket']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['activity', 'minutes', 'intensity', 'level', 'equipment.0']);
    }

    public function test_lists_exercises_with_animation_payload(): void
    {
        $this->getJson('/api/exercises')
            ->assertOk()
            ->assertJsonCount(72, 'data')
            ->assertJsonStructure(['data' => [['slug', 'section', 'splits', 'animation' => ['pattern']]]]);
    }
}
