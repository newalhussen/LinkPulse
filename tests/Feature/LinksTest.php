<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_link_with_a_random_code(): void
    {
        $user = User::factory()->create();
        $res = $this->actingAs($user)->post('/links', ['target_url' => 'https://example.com/some/long/path?x=1']);
        $link = Link::firstOrFail();
        $res->assertRedirect(route('links.show', $link));
        $this->assertMatchesRegularExpression('/^[a-z2-9]{7}$/', $link->code);
        $this->assertSame($user->id, $link->user_id);
    }

    public function test_creates_a_link_with_alias_title_and_expiry(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/links', ['target_url' => 'https://example.com', 'code' => 'Spring-Sale', 'title' => 'Sale', 'expires_on' => now()->addDays(3)->toDateString()])->assertSessionDoesntHaveErrors();
        $link = Link::firstOrFail();
        $this->assertSame('Spring-Sale', $link->code);
        $this->assertTrue($link->expires_at->isFuture());
        $this->assertSame(23, $link->expires_at->hour); // end of the chosen day
    }

    #[DataProvider('badInputs')]
    public function test_rejects_invalid_input(array $input, string $field): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/links', $input + ['target_url' => 'https://example.com'])->assertSessionHasErrors($field);
        $this->assertSame(0, Link::count());
    }

    public static function badInputs(): array
    {
        return [
            'not a url' => [['target_url' => 'not a url'], 'target_url'],
            'javascript scheme' => [['target_url' => 'javascript:alert(1)'], 'target_url'],
            'ftp scheme' => [['target_url' => 'ftp://example.com/file'], 'target_url'],
            'credentials in url' => [['target_url' => 'https://user:pw@example.com'], 'target_url'],
            'own host (loop)' => [['target_url' => 'http://localhost:8000/abc'], 'target_url'],
            'reserved alias' => [['code' => 'login'], 'code'],
            'alias with bad characters' => [['code' => 'bad alias!'], 'code'],
            'alias too short' => [['code' => 'ab'], 'code'],
            'past expiry' => [['expires_on' => '2001-01-01'], 'expires_on'],
        ];
    }

    public function test_aliases_are_unique_case_insensitively(): void
    {
        Link::factory()->create(['code' => 'Promo']);
        $user = User::factory()->create();
        $this->actingAs($user)->post('/links', ['target_url' => 'https://example.com', 'code' => 'promo'])->assertSessionHasErrors('code');
    }

    public function test_dashboard_lists_only_own_links_and_searches(): void
    {
        $me = User::factory()->create();
        Link::factory()->for($me)->create(['title' => 'My brochure']);
        Link::factory()->create(['title' => 'Someone elses secret']);
        $this->actingAs($me)->get('/dashboard')->assertOk()->assertSee('My brochure')->assertDontSee('Someone elses secret');
        $this->actingAs($me)->get('/dashboard?q=zzz')->assertOk()->assertSee('Nothing matches');
    }

    public function test_users_cannot_touch_other_users_links(): void
    {
        $other = Link::factory()->create();
        $me = User::factory()->create();
        $this->actingAs($me)->get(route('links.show', $other))->assertForbidden();
        $this->actingAs($me)->put(route('links.update', $other), ['target_url' => 'https://evil.example'])->assertForbidden();
        $this->actingAs($me)->patch(route('links.toggle', $other))->assertForbidden();
        $this->actingAs($me)->delete(route('links.destroy', $other))->assertForbidden();
        $this->actingAs($me)->get(route('links.qr', $other))->assertForbidden();
        $this->assertDatabaseHas('links', ['id' => $other->id, 'is_active' => true]);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $link = Link::factory()->create();
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get(route('links.show', $link))->assertRedirect('/login');
        $this->post('/links', ['target_url' => 'https://example.com'])->assertRedirect('/login');
    }

    public function test_update_toggle_and_delete(): void
    {
        $me = User::factory()->create();
        $link = Link::factory()->for($me)->create();
        $this->actingAs($me)->put(route('links.update', $link), ['target_url' => 'https://new.example/page', 'title' => 'New'])->assertSessionHasNoErrors();
        $this->assertSame('https://new.example/page', $link->fresh()->target_url);
        $this->actingAs($me)->patch(route('links.toggle', $link));
        $this->assertFalse($link->fresh()->is_active);
        $this->actingAs($me)->delete(route('links.destroy', $link))->assertRedirect('/dashboard');
        $this->assertModelMissing($link);
    }

    public function test_qr_code_is_an_svg_and_can_be_downloaded(): void
    {
        $me = User::factory()->create();
        $link = Link::factory()->for($me)->create(['code' => 'qrtest1']);
        $res = $this->actingAs($me)->get(route('links.qr', $link))->assertOk();
        $this->assertStringContainsString('image/svg+xml', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $res->getContent());
        $dl = $this->actingAs($me)->get(route('links.qr', ['link' => $link, 'download' => 1]));
        $this->assertStringContainsString('attachment; filename="linkpulse-qrtest1.svg"', $dl->headers->get('Content-Disposition'));
    }

    public function test_analytics_page_renders_with_clicks(): void
    {
        $me = User::factory()->create();
        $link = Link::factory()->for($me)->create(['code' => 'stats01']);
        $this->get('/stats01', ['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile', 'Referer' => 'https://news.example/post'])->assertRedirect();
        $this->actingAs($me)->get(route('links.show', $link))->assertOk()->assertSee('news.example');
    }
}
