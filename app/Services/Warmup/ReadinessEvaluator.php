<?php

namespace App\Services\Warmup;

use App\Enums\Impact;
use App\Enums\Intensity;
use App\Enums\ReadinessStatus;

/**
 * Turns the daily readiness answers into routine limits and plain-language
 * guidance. It never diagnoses: it only reflects what the user reported and
 * how today's warm-up is adapted.
 */
class ReadinessEvaluator
{
    public const STOP_SIGNS = 'Stop if you feel sharp pain, instability, locking, significant swelling or anything unusual.';

    public function evaluate(ReadinessInput $input): ReadinessResult
    {
        if (! $input->checked) {
            return new ReadinessResult(
                status: ReadinessStatus::Unchecked,
                score: null,
                headline: 'Readiness check skipped.',
                messages: ['Move with control and build up gradually. '.self::STOP_SIGNS],
                advice: [],
                maxLevel: 3,
                maxIntensity: Intensity::High,
                maxImpact: Impact::High,
                excludeGroups: [],
                reported: [],
                input: $input,
            );
        }

        $score = $input->score ?? 7;
        $kneeGroup = $input->kneePain || $input->kneeSwelling || $input->instability || $input->walkingStairsPain;
        $hipGroup = $input->hipGlutePain || $input->piriformisPain;
        $anyPain = $input->kneePain || $input->hipGlutePain || $input->piriformisPain || $input->walkingStairsPain;

        $reported = array_values(array_filter([
            $input->kneePain ? 'Knee pain' : null,
            $input->kneeSwelling ? 'Knee swelling' : null,
            $input->kneeStiffness ? 'Knee stiffness' : null,
            $input->instability ? 'Instability / giving way' : null,
            $input->hipGlutePain ? 'Hip / glute pain' : null,
            $input->piriformisPain ? 'Pain around the piriformis area' : null,
            $input->walkingStairsPain ? 'Pain walking or on stairs' : null,
        ]));

        if ($anyPain || $input->kneeSwelling || $input->instability || $score <= 3) {
            return $this->caution($input, $score, $kneeGroup, $hipGroup, $reported);
        }

        if ($input->kneeStiffness || $score <= 6) {
            $messages = [];

            if ($input->kneeStiffness) {
                $messages[] = 'You reported knee stiffness. Today’s routine gives extra time to controlled mobility and activation and keeps impact moderate. If the stiffness eases as you warm up, continue as planned; if it increases, or pain or swelling appears, stop.';
            }

            if ($score <= 6) {
                $messages[] = "Moderate readiness today ({$score}/10). Intensity is capped at moderate and the highest level is not used.";
            }

            return new ReadinessResult(
                status: ReadinessStatus::Modify,
                score: $score,
                headline: 'Take it steady — controlled mobility and activation today.',
                messages: $messages,
                advice: [self::STOP_SIGNS],
                maxLevel: 2,
                maxIntensity: Intensity::Moderate,
                maxImpact: Impact::Moderate,
                excludeGroups: [],
                reported: $reported,
                input: $input,
            );
        }

        return new ReadinessResult(
            status: ReadinessStatus::Ready,
            score: $score,
            headline: 'Good readiness — your normal routine.',
            messages: ['Keep every movement controlled and build intensity gradually.'],
            advice: [self::STOP_SIGNS],
            maxLevel: 3,
            maxIntensity: Intensity::High,
            maxImpact: Impact::High,
            excludeGroups: [],
            reported: $reported,
            input: $input,
        );
    }

    private function caution(ReadinessInput $input, int $score, bool $kneeGroup, bool $hipGroup, array $reported): ReadinessResult
    {
        $messages = [];

        if ($input->kneePain) {
            $messages[] = 'You reported knee pain today. Exercises that load the knee more (step-ups, lunges, skipping, kneeling drills) are removed and impact is kept low. Only continue with movements that are pain-free or within a mild, non-worsening range.';
        }

        if ($input->kneeSwelling) {
            $messages[] = 'You reported knee swelling. Today’s routine avoids impact, jumping and fast changes of direction. Swelling that persists or keeps returning after activity is worth discussing with your physiotherapist or doctor.';
        }

        if ($input->instability) {
            $messages[] = 'You reported a feeling of instability or giving way. Today’s routine avoids impact, pivoting and fast direction changes. If this feeling recurs, arrange a professional assessment before returning to sport.';
        }

        if ($input->hipGlutePain || $input->piriformisPain) {
            $messages[] = 'You reported hip/glute or piriformis-area pain. Deeper hip-rotation positions (90/90, figure-4) are removed; do the remaining hip exercises in a small, comfortable range. Stop if pain spreads down the leg or you notice tingling or numbness.';
        }

        if ($input->walkingStairsPain) {
            $messages[] = 'You reported pain when walking or on stairs. Keep today’s session low-impact and gentle.';
        }

        if ($score <= 3) {
            $messages[] = "Low readiness today ({$score}/10). The routine is kept light and low-impact.";
        }

        $excludeGroups = array_values(array_filter([
            $kneeGroup ? 'knee' : null,
            $hipGroup ? 'hip' : null,
        ]));

        return new ReadinessResult(
            status: ReadinessStatus::Caution,
            score: $score,
            headline: 'Caution — today’s warm-up has been adjusted to light, low-impact work.',
            messages: $messages,
            advice: [
                'Consider postponing high-impact or high-intensity activity today (sprinting, jumping, cutting, heavy lifting) or choosing a lighter session.',
                'If symptoms persist or worsen, or you notice swelling, locking or giving way, seek assessment from a physiotherapist or doctor.',
                'This check does not diagnose anything — it only adapts today’s warm-up to what you reported.',
            ],
            maxLevel: 1,
            maxIntensity: Intensity::Light,
            maxImpact: Impact::Low,
            excludeGroups: $excludeGroups,
            reported: $reported,
            input: $input,
        );
    }
}
