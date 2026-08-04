<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_can_be_rendered(): void
    {
        $this->get(route('contact.create'))
            ->assertOk()
            ->assertSee('Contact us')
            ->assertSee('Send message');
    }

    public function test_contact_form_sends_mail(): void
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

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
            return $mail->hasTo('admin@example.com')
                && $mail->name === 'Chris Angler'
                && $mail->email === 'chris@example.com'
                && $mail->subjectLine === 'Missing venue'
                && str_contains($mail->messageBody, 'Wellfield');
        });
    }

    public function test_contact_form_rejects_honeypot_submissions(): void
    {
        Mail::fake();

        config(['mail.contact_to' => 'admin@example.com']);

        $this->from(route('contact.create'))
            ->post(route('contact.store'), [
                'name' => 'Bot',
                'email' => 'bot@example.com',
                'subject' => 'Spam',
                'message' => 'Buy followers',
                'website' => 'https://spam.test',
            ])
            ->assertRedirect(route('contact.create'))
            ->assertSessionHasErrors('website');

        Mail::assertNothingSent();
    }

    public function test_contact_form_requires_fields(): void
    {
        Mail::fake();

        config(['mail.contact_to' => 'admin@example.com']);

        $this->from(route('contact.create'))
            ->post(route('contact.store'), [])
            ->assertRedirect(route('contact.create'))
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        Mail::assertNothingSent();
    }
}
