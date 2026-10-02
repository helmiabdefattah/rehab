<?php

namespace Tests\Unit;

use App\Enums\Activity;
use PHPUnit\Framework\TestCase;

class SplitEnumTest extends TestCase
{
    public function test_the_four_training_splits_exist(): void
    {
        $values = array_map(fn (Activity $a) => $a->value, Activity::cases());

        $this->assertSame(['push', 'pull', 'legs', 'cardio-core'], $values);
    }

    public function test_each_split_has_a_label_emoji_and_muscles(): void
    {
        foreach (Activity::cases() as $split) {
            $this->assertNotEmpty($split->shortLabel());
            $this->assertNotEmpty($split->label());
            $this->assertNotEmpty($split->emoji());
            $this->assertNotEmpty($split->muscles());
            $this->assertNotEmpty($split->description());
        }
    }
}
