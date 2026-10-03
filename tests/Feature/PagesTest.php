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
            'home splits' => ['/', 'What are you training today?'],
            'train push' => ['/train/push', 'Start Push warm-up'],
            'train cardio-core' => ['/train/cardio-core', 'Cardio &amp; Core'],
            'builder' => ['/warm-up', 'What are you training today?'],
            'builder preselected' => ['/warm-up?activity=pull', 'data-preselected="pull"'],
            'quick 5' => ['/warm-up/quick/5', 'data-quick="5"'],
            'quick 10' => ['/warm-up/quick/10', 'data-autostart="1"'],
            'quick 15' => ['/warm-up/quick/15', 'data-quick="15"'],
            'library' => ['/exercises', 'View animation'],
            'library filtered' => ['/exercises?section=workout', 'data-library-grid'],
            'warm-up exercise' => ['/exercises/glute-bridge', 'data-animation='],
            'workout exercise' => ['/exercises/barbell-bench-press', 'Barbell Bench Press'],
            'timer' => ['/timer', 'Interval'],
            'progress' => ['/progress', 'Progress'],
            'settings' => ['/settings', 'Export backup'],
            'safety' => ['/safety', 'not a medical device or a substitute'],
            'sources' => ['/sources', 'doi:10.1136/bmj.a2469'],
        ];
    }

    #[DataProvider('pages')]
    public function test_page_renders(string $url, string $expected): void
    {
        $this->get($url)->assertOk()->assertSee($expected, false);
    }

    public function test_unknown_routes_return_404(): void
    {
        $this->get('/warm-up/quick/7')->assertNotFound();
        $this->get('/exercises/does-not-exist')->assertNotFound();
        $this->get('/train/arms')->assertNotFound();
    }

    public function test_animation_payload_attribute_is_valid_json(): void
    {
        $html = $this->get('/exercises/glute-bridge')->getContent();

        preg_match('/data-animation-payload="([^"]+)"/', $html, $m);
        $payload = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);

        $this->assertSame('Glute Bridge', $payload['exercise']);
        $this->assertNotEmpty($payload['pattern']);
    }

    public function test_quick_page_renders_the_client_build_shell(): void
    {
        // Quick warm-ups are now built in the browser (offline-capable), so the
        // page is just the builder shell flagged to auto-start.
        $this->get('/warm-up/quick/10')
            ->assertOk()
            ->assertSee('data-quick="10"', false)
            ->assertSee('data-autostart="1"', false);
    }
}
