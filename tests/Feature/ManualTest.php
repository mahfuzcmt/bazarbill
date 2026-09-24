<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_page_is_public_and_in_bangla(): void
    {
        $this->get(route('manual'))->assertOk()
            ->assertSee('ব্যবহার সহায়িকা')->assertSee('মার্কেট মালিকের গাইড')->assertSee('দোকানদারের গাইড')->assertSee('01805995662');
    }

    public function test_manual_pdf_downloads(): void
    {
        $response = $this->get(route('manual.pdf'));
        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
