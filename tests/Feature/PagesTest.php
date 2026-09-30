<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public static function pages(): array
    {
        return [
            'home' => ['/', 'QUICK 10-MIN WARM-UP'],
            'builder' => ['/warm-up', 'How do you feel today?'],
            'builder preselected' => ['/warm-up?activity=running', 'data-preselected="running"'],
            'quick 5' => ['/warm-up/quick/5', 'id="routine-data"'],
            'quick 10' => ['/warm-up/quick/10?level=2&equipment=mini-band', 'data-autostart="1"'],
            'quick 15' => ['/warm-up/quick/15', '15-Min Complete'],
            'knee focus' => ['/warm-up/focus/knee', 'Not automatically prescribed'],
            'hip focus' => ['/warm-up/focus/hip', 'not forcing the piriformis'],
            'focus start' => ['/warm-up/focus/knee/start/5', 'id="routine-data"'],
            'library' => ['/exercises', 'Watch Video'],
            'library filtered' => ['/exercises?tag=core', 'data-library-grid'],
            'exercise' => ['/exercises/glute-bridge', 'youtube-nocookie.com/embed/'],
            'exercise without video' => ['/exercises/calf-raises', 'Find a Video'],
            'timer' => ['/timer', 'Interval'],
            'progress' => ['/progress', 'Progression check'],
            'settings' => ['/settings', 'Export backup'],
            'safety' => ['/safety', 'not a medical device or substitute for individualized medical or physiotherapy advice'],
            'sources' => ['/sources', 'doi:10.1136/bmj.a2469'],
        ];
    }

    #[DataProvider('pages')]
    public function test_page_renders(string $url, string $expected): void
    {
        $this->get($url)->assertOk()->assertSee($expected, false);
    }

    public function test_unknown_quick_duration_and_exercise_return_404(): void
    {
        $this->get('/warm-up/quick/7')->assertNotFound();
        $this->get('/exercises/does-not-exist')->assertNotFound();
        $this->get('/warm-up/focus/shoulder')->assertNotFound();
    }

    public function test_video_payload_attribute_is_valid_json(): void
    {
        $html = $this->get('/exercises/glute-bridge')->getContent();

        preg_match('/data-video="([^"]+)"/', $html, $m);
        $payload = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);

        $this->assertSame('Glute Bridge', $payload['exercise']);
        $this->assertSame('WtilA9IJX1c', $payload['id']);
    }

    public function test_quick_links_use_preferences_from_query(): void
    {
        $html = $this->get('/warm-up/quick/10?level=3&equipment=bike,mini-band&transition=0')->getContent();
        preg_match('~<script type="application/json" id="routine-data">(.*?)</script>~s', $html, $m);
        $routine = json_decode($m[1], true);

        $this->assertSame(3, $routine['level']);
        $this->assertSame(0, $routine['transition_seconds']);
        $this->assertSame(600, $routine['total_seconds']);
        $this->assertSame('quick', $routine['source']);
    }
}
