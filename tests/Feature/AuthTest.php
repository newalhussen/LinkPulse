<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_logs_in_and_hashes_the_password(): void
    {
        $this->post('/register', ['name' => 'Ann', 'email' => 'ann@example.com', 'password' => 'correct horse 42', 'password_confirmation' => 'correct horse 42'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertNotSame('correct horse 42', User::firstOrFail()->password);
    }

    public function test_weak_or_mismatched_passwords_are_rejected(): void
    {
        $this->post('/register', ['name' => 'A', 'email' => 'a@example.com', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
        $this->post('/register', ['name' => 'A', 'email' => 'a@example.com', 'password' => 'long enough pw 1', 'password_confirmation' => 'different pw 1'])->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'a-good-password']);
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'a-good-password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'nope']);
        }
        $this->post('/login', ['email' => $user->email, 'password' => 'nope'])->assertStatus(429);
    }
}
