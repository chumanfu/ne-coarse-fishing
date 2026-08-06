<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Suggest a club edit</h1>
        <p class="text-slate-600 mt-1">{{ $club->name }} — an admin will review your suggestion.</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('clubs.suggest-edit.store', $club) }}" class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf
            @include('clubs._form', ['club' => $club, 'withLogo' => false, 'withMessage' => true])
            <button type="submit" class="px-5 py-3 rounded-md bg-sky-800 text-white font-bold hover:bg-sky-900">Submit suggestion</button>
        </form>
    </div>
</x-app-layout>
