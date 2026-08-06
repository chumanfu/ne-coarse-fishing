<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Suggest a tackle shop edit</h1>
        <p class="text-slate-600 mt-1">{{ $shop->name }} — an admin will review your suggestion.</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('tackle-shops.suggest-edit.store', $shop) }}" class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf
            @include('tackle-shops._form', ['shop' => $shop, 'withLogo' => false, 'withMessage' => true])
            <button type="submit" class="px-5 py-3 rounded-md bg-sky-800 text-white font-bold hover:bg-sky-900">Submit suggestion</button>
        </form>
    </div>
</x-app-layout>
