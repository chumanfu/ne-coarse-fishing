<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use App\Mail\MessageReplyNotification;
use App\Models\MessageThread;
use App\Models\User;
use App\Services\MessagingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_creates_thread_and_emails_admin(): void
    {
        Mail::fake();
        config(['mail.contact_to' => 'admin@example.com']);

        $this->post(route('contact.store'), [
            'name' => 'Chris Angler',
            'email' => 'chris@example.com',
            'subject' => 'Missing venue',
            'message' => 'Could you add Wingate Wellfield Lake?',
            'website' => '',
        ])
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('message_threads', [
            'contact_email' => 'chris@example.com',
            'subject' => 'Missing venue',
            'source' => 'contact',
        ]);

        $this->assertDatabaseHas('messages', [
            'body' => 'Could you add Wingate Wellfield Lake?',
            'is_from_admin' => false,
        ]);

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
            return $mail->hasTo('admin@example.com')
                && $mail->thread instanceof MessageThread;
        });
    }

    public function test_logged_in_contact_redirects_to_thread(): void
    {
        Mail::fake();
        config(['mail.contact_to' => 'admin@example.com']);
        Role::findOrCreate('angler');

        $user = User::factory()->create([
            'name' => 'Logged Angler',
            'email' => 'logged@example.com',
        ]);
        $user->assignRole('angler');

        $response = $this->actingAs($user)->post(route('contact.store'), [
            'name' => 'Logged Angler',
            'email' => 'logged@example.com',
            'subject' => 'Peg question',
            'message' => 'Is peg 12 still open?',
            'website' => '',
        ]);

        $thread = MessageThread::query()->first();
        $this->assertNotNull($thread);
        $this->assertSame($user->id, $thread->user_id);

        $response->assertRedirect(route('messages.show', $thread));
    }

    public function test_admin_can_message_and_angler_can_reply(): void
    {
        Mail::fake();
        config(['mail.contact_to' => 'admin@example.com']);

        Role::findOrCreate('super_admin');
        Role::findOrCreate('angler');

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $angler = User::factory()->create();
        $angler->assignRole('angler');

        $thread = app(MessagingService::class)->startWithUser(
            $admin,
            $angler,
            'Welcome aboard',
            'Thanks for joining NE Coarse Fishing.',
        );

        Mail::assertSent(MessageReplyNotification::class, function (MessageReplyNotification $mail) use ($angler) {
            return $mail->hasTo($angler->email) && $mail->forAdmin === false;
        });

        $this->actingAs($angler)
            ->get(route('messages.show', $thread))
            ->assertOk()
            ->assertSee('Welcome aboard')
            ->assertSee('Thanks for joining');

        $this->actingAs($angler)
            ->post(route('messages.reply', $thread), [
                'body' => 'Cheers Chris!',
            ])
            ->assertRedirect(route('messages.show', $thread));

        $this->assertDatabaseHas('messages', [
            'message_thread_id' => $thread->id,
            'body' => 'Cheers Chris!',
            'is_from_admin' => false,
        ]);

        Mail::assertSent(MessageReplyNotification::class, function (MessageReplyNotification $mail) {
            return $mail->hasTo('admin@example.com') && $mail->forAdmin === true;
        });
    }

    public function test_admin_can_broadcast_inbox_message_to_all_users(): void
    {
        Mail::fake();
        Role::findOrCreate('super_admin');
        Role::findOrCreate('angler');

        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('super_admin');

        $a = User::factory()->create(['email' => 'a@example.com']);
        $b = User::factory()->create(['email' => 'b@example.com']);
        $a->assignRole('angler');
        $b->assignRole('angler');

        $expected = User::query()->whereKeyNot($admin->id)->count();

        $count = app(MessagingService::class)->broadcastToAllUsers(
            $admin,
            'Site update',
            'Please check the new venue maps.',
        );

        $this->assertSame($expected, $count);
        $this->assertGreaterThanOrEqual(2, $count);
        $this->assertDatabaseCount('message_threads', $expected);
        $this->assertDatabaseHas('message_threads', [
            'user_id' => $a->id,
            'subject' => 'Site update',
            'source' => 'admin',
        ]);
        $this->assertDatabaseHas('message_threads', [
            'user_id' => $b->id,
            'subject' => 'Site update',
            'source' => 'admin',
        ]);
        $this->assertDatabaseMissing('message_threads', [
            'user_id' => $admin->id,
            'subject' => 'Site update',
        ]);

        Mail::assertQueued(MessageReplyNotification::class, $expected);
    }

    public function test_angler_cannot_view_another_users_thread(): void
    {
        Role::findOrCreate('angler');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $owner->assignRole('angler');
        $other->assignRole('angler');

        $thread = MessageThread::factory()->forUser($owner)->create();

        $this->actingAs($other)
            ->get(route('messages.show', $thread))
            ->assertForbidden();
    }
}
