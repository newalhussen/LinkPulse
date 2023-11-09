<?php

namespace Tests\Feature;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36'];

    public function test_redirects_and_counts_the_click(): void
    {
        $link = Link::factory()->create(['code' => 'go-here', 'target_url' => 'https://example.org/landing']);
        $this->get('/go-here', self::BROWSER + ['Referer' => 'https://twitter.com/some/status/123?utm=1'])
            ->assertRedirect('https://example.org/landing')->assertStatus(302);
        $this->assertSame(1, $link->fresh()->clicks_count);
        $click = Click::firstOrFail();
        $this->assertSame('twitter.com', $click->referrer_host); // host only, never the full URL
        $this->assertSame('desktop', $click->device);
    }

    public function test_direct_visits_have_no_referrer_and_own_site_is_ignored(): void
    {
        Link::factory()->create(['code' => 'direct1']);
        $this->get('/direct1', self::BROWSER);
        $this->get('/direct1', self::BROWSER + ['Referer' => 'http://localhost:8000/dashboard']);
        $this->assertSame([null, null], Click::pluck('referrer_host')->all());
    }

    public function test_no_personal_data_is_stored(): void
    {
        $this->assertSame(['id', 'link_id', 'clicked_at', 'referrer_host', 'device'], Schema::getColumnListing('clicks'));
    }

    public function test_bots_are_redirected_but_not_counted(): void
    {
        $link = Link::factory()->create(['code' => 'botlink']);
        foreach (['Googlebot/2.1', 'facebookexternalhit/1.1', 'curl/8.0', 'Slackbot-LinkExpanding 1.0'] as $ua) {
            $this->get('/botlink', ['User-Agent' => $ua])->assertRedirect();
        }
        $this->assertSame(0, $link->fresh()->clicks_count);
        $this->assertSame(0, Click::count());
    }

    public function test_unknown_code_is_404(): void
    {
        $this->get('/nope-nope')->assertNotFound();
    }

    public function test_expired_link_returns_410_and_is_not_counted(): void
    {
        $link = Link::factory()->create(['code' => 'old-one', 'expires_at' => now()->subMinute()]);
        $this->get('/old-one', self::BROWSER)->assertStatus(410)->assertSee('no longer available');
        $this->assertSame(0, $link->fresh()->clicks_count);
    }

    public function test_disabled_link_returns_410(): void
    {
        Link::factory()->create(['code' => 'off-one', 'is_active' => false]);
        $this->get('/off-one', self::BROWSER)->assertStatus(410);
    }

    public function test_link_expiring_in_the_future_still_works(): void
    {
        Link::factory()->create(['code' => 'later1', 'expires_at' => now()->addDay()]);
        $this->get('/later1', self::BROWSER)->assertRedirect();
    }

    public function test_application_paths_are_not_treated_as_short_codes(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_editing_the_destination_takes_effect_immediately(): void
    {
        $link = Link::factory()->create(['code' => 'edit-me', 'target_url' => 'https://a.example']);
        $this->get('/edit-me', self::BROWSER)->assertRedirect('https://a.example');
        $link->update(['target_url' => 'https://b.example']);
        $this->get('/edit-me', self::BROWSER)->assertRedirect('https://b.example');
    }
}
