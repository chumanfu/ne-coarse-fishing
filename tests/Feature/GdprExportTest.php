<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GdprExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_download_gdpr_data_export(): void
    {
        Mail::fake();
        config(['mail.contact_to' => 'admin@example.com']);
        Role::findOrCreate('angler');

        $user = User::factory()->create([
            'name' => 'Data Subject',
            'email' => 'subject@example.com',
        ]);
        $user->assignRole('angler');

        MessageThread::factory()->forUser($user)->create([
            'subject' => 'Hello from admin',
        ])->messages()->create([
            'user_id' => $user->id,
            'body' => 'Private conversation content',
            'is_from_admin' => false,
        ]);

        $response = $this->actingAs($user)
            ->post(route('profile.data-export'));

        $response->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('application/json', (string) $response->headers->get('content-type'));

        $json = $response->streamedContent();
        $this->assertStringContainsString('Data Subject', $json);
        $this->assertStringContainsString('subject@example.com', $json);
        $this->assertStringContainsString('Private conversation content', $json);
        $this->assertStringContainsString('Hello from admin', $json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('account', $decoded);
        $this->assertArrayNotHasKey('password', $decoded['account']);

        $this->assertDatabaseHas('message_threads', [
            'user_id' => $user->id,
            'subject' => 'GDPR data export requested',
        ]);

        Mail::assertSent(ContactMessage::class);
    }

    public function test_guest_cannot_export_data(): void
    {
        $this->post(route('profile.data-export'))
            ->assertRedirect(route('login'));
    }
}
