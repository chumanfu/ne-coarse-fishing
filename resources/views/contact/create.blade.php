<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Contact us</h1>
        <p class="text-slate-600 mt-1">Questions about the site, missing venues, or something that needs fixing — send us a message.</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('contact.store') }}" class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold mb-1">Name</label>
                <input id="name" name="name" type="text" required maxlength="120" value="{{ $name }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                @error('name')
                    <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold mb-1">Email</label>
                <input id="email" name="email" type="email" required maxlength="255" value="{{ $email }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                @error('email')
                    <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subject" class="block text-sm font-semibold mb-1">Subject</label>
                <input id="subject" name="subject" type="text" required maxlength="160" value="{{ old('subject') }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                @error('subject')
                    <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-semibold mb-1">Message</label>
                <textarea id="message" name="message" rows="8" required maxlength="5000" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Honeypot for bots --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="px-5 py-3 rounded-md bg-sky-800 text-white font-bold hover:bg-sky-900">
                Send message
            </button>
        </form>
    </div>
</x-app-layout>
