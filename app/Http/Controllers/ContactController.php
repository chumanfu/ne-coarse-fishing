<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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

    public function store(Request $request): RedirectResponse
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

        $to = config('mail.contact_to');

        if (! filled($to)) {
            return back()
                ->withInput($request->except('website'))
                ->withErrors(['email' => 'Contact email is not configured yet. Please try again later.']);
        }

        Mail::to($to)->send(new ContactMessage(
            name: $validated['name'],
            email: $validated['email'],
            subjectLine: $validated['subject'],
            messageBody: $validated['message'],
        ));

        return redirect()
            ->route('contact.create')
            ->with('status', 'Thanks — your message has been sent. We will get back to you soon.');
    }
}
