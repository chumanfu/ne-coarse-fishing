<?php

namespace App\Http\Controllers;

use App\Services\MessagingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact.create', [
            'name' => old('name', auth()->user()?->name),
            'email' => old('email', auth()->user()?->email),
        ]);
    }

    public function store(Request $request, MessagingService $messaging): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'max:0'],
        ], [
            'website.max' => 'Unable to send your message.',
        ]);

        if (! filled(config('mail.contact_to'))) {
            return back()
                ->withInput($request->except('website'))
                ->withErrors(['email' => 'Contact email is not configured yet. Please try again later.']);
        }

        $thread = $messaging->createFromContact(
            name: $validated['name'],
            email: $validated['email'],
            subject: $validated['subject'],
            body: $validated['message'],
            user: $request->user(),
        );

        app(\App\Services\ActivityLogger::class)->messageReceived($thread, $request->user());

        $redirect = $request->user()
            ? redirect()->route('messages.show', $thread)
            : redirect()->route('contact.create');

        return $redirect->with('status', 'Thanks — your message has been sent. We will get back to you soon.');
    }
}
