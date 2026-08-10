<?php

namespace Tests\Feature\Auth;

use App\Mail\MessageReplyNotification;
use App\Models\Activity;
use App\Models\Message;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
            ->assertSee('dark:text-slate-200', false)
            ->assertSee('dark:text-slate-300', false)
            ->assertSee('Already registered?')
            ->assertSee('Club memberships');
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

        $this->assertSame(
            1,
            Message::query()
                ->where('is_from_admin', true)
                ->whereHas('thread', fn ($q) => $q->where('user_id', $user->id)->where('subject', 'Welcome to NE Coarse Fishing'))
                ->count()
        );

        Mail::assertQueued(MessageReplyNotification::class, function (MessageReplyNotification $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->forAdmin === false
                && $mail->thread->subject === 'Welcome to NE Coarse Fishing';
        });
        Mail::assertQueued(MessageReplyNotification::class, 1);
    }

    public function test_registration_and_claims_appear_in_activity_feed_for_other_users(): void
    {
        Mail::fake();
        Role::findOrCreate('super_admin');
        Role::findOrCreate('angler');

        $admin = User::factory()->create(['email' => 'welcome-admin@example.com']);
        $admin->assignRole('super_admin');

        $this->post('/register', [
            'name' => 'Other Angler',
            'email' => 'other-angler@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $other = User::query()->where('email', 'other-angler@example.com')->firstOrFail();

        $this->assertDatabaseHas('activities', [
            'type' => Activity::TYPE_USER_REGISTERED,
            'user_id' => $other->id,
            'title' => 'Other Angler joined NE Coarse Fishing',
        ]);

        $this->assertSame(
            1,
            Activity::query()
                ->where('type', Activity::TYPE_USER_REGISTERED)
                ->where('user_id', $other->id)
                ->count()
        );

        $venue = Venue::factory()->create(['is_approved' => true, 'manager_id' => null]);

        $this->actingAs($other)
            ->post(route('venues.claim', $venue), ['message' => 'I run this lake'])
            ->assertRedirect(route('venues.show', $venue));

        $this->assertDatabaseHas('activities', [
            'type' => Activity::TYPE_VENUE_CLAIM,
            'user_id' => $other->id,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\LatestActivityTable::class)
            ->assertSee('Other Angler joined NE Coarse Fishing')
            ->assertSee('Other Angler claimed '.$venue->name);
    }
}
