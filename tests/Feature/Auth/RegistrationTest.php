<?php

namespace Tests\Feature\Auth;

use App\Mail\MessageReplyNotification;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
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
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_users_receive_a_welcome_inbox_message(): void
    {
        Mail::fake();
        Role::findOrCreate('super_admin');
        Role::findOrCreate('angler');

        $admin = User::factory()->create(['email' => 'welcome-admin@example.com']);
        $admin->assignRole('super_admin');

        $this->post('/register', [
            'name' => 'New Angler',
            'email' => 'new-angler@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'new-angler@example.com')->firstOrFail();

        $this->assertDatabaseHas('message_threads', [
            'user_id' => $user->id,
            'subject' => 'Welcome to NE Coarse Fishing',
            'source' => 'admin',
        ]);

        $message = Message::query()
            ->where('is_from_admin', true)
            ->whereHas('thread', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        $this->assertNotNull($message);
        $this->assertStringContainsString('Venues & map', $message->body);
        $this->assertStringContainsString('Fishing sessions', $message->body);
        $this->assertStringContainsString('Tight lines', $message->body);

        Mail::assertQueued(MessageReplyNotification::class, function (MessageReplyNotification $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->forAdmin === false
                && $mail->thread->subject === 'Welcome to NE Coarse Fishing';
        });
    }
}
