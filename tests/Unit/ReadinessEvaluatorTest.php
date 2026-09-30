<?php

namespace Tests\Unit;

use App\Enums\Impact;
use App\Enums\Intensity;
use App\Enums\ReadinessStatus;
use App\Services\Warmup\ReadinessEvaluator;
use App\Services\Warmup\ReadinessInput;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReadinessEvaluatorTest extends TestCase
{
    private function evaluate(array $answers)
    {
        return (new ReadinessEvaluator)->evaluate(ReadinessInput::fromArray(['checked' => true, ...$answers]));
    }

    public function test_good_readiness_keeps_the_normal_routine(): void
    {
        $result = $this->evaluate(['score' => 8]);

        $this->assertSame(ReadinessStatus::Ready, $result->status);
        $this->assertSame(3, $result->maxLevel);
        $this->assertSame(Intensity::High, $result->maxIntensity);
        $this->assertSame([], $result->excludeGroups);
    }

    public function test_mild_stiffness_only_gives_controlled_routine(): void
    {
        $result = $this->evaluate(['knee_stiffness' => true, 'score' => 8]);

        $this->assertSame(ReadinessStatus::Modify, $result->status);
        $this->assertSame(2, $result->maxLevel);
        $this->assertSame(Intensity::Moderate, $result->maxIntensity);
        $this->assertSame(Impact::Moderate, $result->maxImpact);
    }

    public function test_moderate_score_modifies(): void
    {
        $this->assertSame(ReadinessStatus::Modify, $this->evaluate(['score' => 5])->status);
    }

    public static function cautionCases(): array
    {
        return [
            'knee pain' => [['knee_pain' => true], ['knee']],
            'swelling' => [['knee_swelling' => true], ['knee']],
            'instability' => [['instability' => true], ['knee']],
            'stairs pain' => [['walking_stairs_pain' => true], ['knee']],
            'hip/glute pain' => [['hip_glute_pain' => true], ['hip']],
            'piriformis pain' => [['piriformis_pain' => true], ['hip']],
            'knee + hip' => [['knee_pain' => true, 'piriformis_pain' => true], ['knee', 'hip']],
            'low score' => [['score' => 2], []],
        ];
    }

    #[DataProvider('cautionCases')]
    public function test_pain_or_instability_triggers_caution(array $answers, array $groups): void
    {
        $result = $this->evaluate($answers + ['score' => 8]);

        $this->assertSame(ReadinessStatus::Caution, $result->status);
        $this->assertSame(1, $result->maxLevel);
        $this->assertSame(Intensity::Light, $result->maxIntensity);
        $this->assertSame(Impact::Low, $result->maxImpact);
        $this->assertSame($groups, $result->excludeGroups);
        $this->assertNotEmpty($result->messages);
        $this->assertStringContainsString('seek assessment', implode(' ', $result->advice));
    }

    public function test_skipped_check_does_not_limit_but_reminds_about_stop_signs(): void
    {
        $result = (new ReadinessEvaluator)->evaluate(ReadinessInput::unchecked());

        $this->assertSame(ReadinessStatus::Unchecked, $result->status);
        $this->assertStringContainsString('Stop if', implode(' ', $result->messages));
    }

    public function test_wording_never_diagnoses_or_labels_the_user_as_injured(): void
    {
        $result = $this->evaluate([
            'knee_pain' => true, 'knee_swelling' => true, 'instability' => true,
            'hip_glute_pain' => true, 'piriformis_pain' => true, 'walking_stairs_pain' => true, 'score' => 2,
        ]);

        $text = mb_strtolower(implode(' ', [$result->headline, ...$result->messages, ...$result->advice]));

        foreach (['you have', 'you are injured', 'your injury', 'tear', 'syndrome', 'diagnosis:'] as $phrase) {
            $this->assertStringNotContainsString($phrase, $text);
        }
        $this->assertStringContainsString('does not diagnose', $text);
    }
}
