<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = Volt::test('auth.register')
            ->set('username', 'testuser')
            ->set('email', 'test@example.com')
            ->set('password', 'Password1')
            ->set('password_confirmation', 'Password1')
            ->call('register');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_registration_is_rate_limited_by_ip(): void
    {
        RateLimiter::increment('register|127.0.0.1', 3600, 10);

        Volt::test('auth.register')
            ->set('username', 'testuser')
            ->set('email', 'test@example.com')
            ->set('password', 'Password1')
            ->set('password_confirmation', 'Password1')
            ->call('register')
            ->assertHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_weak_passwords_are_rejected(): void
    {
        Volt::test('auth.register')
            ->set('username', 'testuser')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasErrors('password');

        $this->assertGuest();
    }
}
