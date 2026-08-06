<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    public function test_login_and_register_pages_show_google_button(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in with Google')
            ->assertSee(route('auth.google'), false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Sign up with Google')
            ->assertSee(route('auth.google'), false);
    }

    public function test_google_redirect_sends_user_to_provider(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('scopes')->once()->with(['openid', 'profile', 'email'])->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_google_callback_creates_new_user_and_logs_in(): void
    {
        Event::fake([Registered::class]);
        Role::findOrCreate('angler');

        $this->mockGoogleUser([
            'id' => 'google-123',
            'name' => 'Chris Angler',
            'email' => 'chris@example.com',
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'chris@example.com',
            'google_id' => 'google-123',
            'name' => 'Chris Angler',
        ]);

        $user = User::query()->where('email', 'chris@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('angler'));
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->password);
        Event::assertDispatched(Registered::class);
    }

    public function test_google_callback_links_existing_email_account(): void
    {
        Role::findOrCreate('angler');
        $existing = User::factory()->create([
            'email' => 'chris@example.com',
            'name' => 'Existing Chris',
        ]);
        $existing->assignRole('angler');

        $this->mockGoogleUser([
            'id' => 'google-456',
            'name' => 'Chris Google',
            'email' => 'chris@example.com',
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertSame('google-456', $existing->fresh()->google_id);
        $this->assertSame(1, User::query()->where('email', 'chris@example.com')->count());
    }

    public function test_google_callback_logs_in_existing_google_user(): void
    {
        Role::findOrCreate('angler');
        $user = User::factory()->create([
            'email' => 'chris@example.com',
            'google_id' => 'google-789',
            'password' => null,
        ]);
        $user->assignRole('angler');

        $this->mockGoogleUser([
            'id' => 'google-789',
            'name' => 'Chris Angler',
            'email' => 'chris@example.com',
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'google-789',
        ]);
        $this->assertSame(1, User::query()->where('google_id', 'google-789')->count());
    }

    /**
     * @param  array{id: string, name: string, email: string}  $attributes
     */
    private function mockGoogleUser(array $attributes): void
    {
        $socialiteUser = (new SocialiteUser)->map([
            'id' => $attributes['id'],
            'nickname' => null,
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'avatar' => null,
            'avatar_original' => null,
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
    }
}
