<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LegacyPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_and_diagnostic_pages_are_not_public(): void
    {
        Http::fake();

        foreach (['/cricbuzz-test', '/cricbuzz-test/165061', '/cricbuzz-results', '/cricket-test', '/cricket-test/123'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }

        Http::assertNothingSent();
    }
}
