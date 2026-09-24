<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_declare_the_web_app_manifest_and_icons(): void
    {
        $this->get('/')->assertOk()->assertSee('manifest.webmanifest')->assertSee('apple-touch-icon.png');
        $this->get('/login')->assertOk()->assertSee('manifest.webmanifest')->assertSee('theme-color');

        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);
        $this->assertSame('DueTap', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path($icon['src']));
        }
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
    }
}
