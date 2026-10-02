<?php

namespace App\Http\Requests;

use App\Enums\Activity;
use App\Enums\Intensity;
use App\Services\Warmup\ReadinessInput;
use App\Services\Warmup\RoutineRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateRoutineRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'activity' => ['required', Rule::enum(Activity::class)],
            'minutes' => ['required', 'integer', Rule::in(config('warmup.durations'))],
            'intensity' => ['required', Rule::enum(Intensity::class)],
            'level' => ['required', 'integer', 'between:1,3'],
            'equipment' => ['sometimes', 'array'],
            'equipment.*' => ['string', Rule::in(array_keys(config('warmup.equipment')))],
            'transition' => ['sometimes', 'integer', 'between:0,15'],
        ];
    }

    public function toRoutineRequest(): RoutineRequest
    {
        $activity = Activity::from($this->validated('activity'));

        return new RoutineRequest(
            program: $activity->value,
            activity: $activity,
            minutes: (int) $this->validated('minutes'),
            intensity: Intensity::from($this->validated('intensity')),
            level: (int) $this->validated('level'),
            equipment: array_values($this->validated('equipment', [])),
            readiness: ReadinessInput::unchecked(),
            transitionSeconds: (int) $this->validated('transition', config('warmup.transition_seconds')),
            source: 'builder',
        );
    }
}
