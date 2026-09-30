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

            {{-- Hidden honeypot: bots often fill every text field. --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>

            <fieldset class="rounded-lg border-2 border-slate-300 p-4">
                <legend class="px-1 text-sm font-semibold text-slate-900">Are you a human?</legend>
                <p class="text-xs text-slate-600 mb-3">This stops automated messages and sales pitches. The form is for anglers and site questions only.</p>
                <label for="human" class="flex items-start gap-3 min-h-11 cursor-pointer">
                    <input
                        id="human"
                        name="human"
                        type="checkbox"
                        value="1"
                        required
                        @checked(old('human'))
                        class="mt-1 h-5 w-5 rounded border-2 border-slate-400 text-sky-700 focus:ring-sky-700"
                    >
                    <span class="text-sm font-semibold text-slate-800">Yes — I am a person, not offering web, SEO, or other paid services.</span>
                </label>
                @error('human')
                    <p class="text-sm text-red-700 mt-2">{{ $message }}</p>
                @enderror
            </fieldset>

            <button type="submit" class="px-5 py-3 rounded-md bg-sky-800 text-white font-bold hover:bg-sky-900">
                Send message
            </button>
        </form>
    </div>
</x-app-layout>
