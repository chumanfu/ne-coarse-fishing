<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Write a tackle review</h1>
        <p class="text-slate-600 mt-1">Rate gear from 0–5 stars, add photos, and link where you bought it.</p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('tackle-reviews.store') }}" enctype="multipart/form-data" class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4">
            @csrf
            @include('tackle-reviews._form')
            <button class="px-5 py-3 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">Publish review</button>
        </form>
    </div>
</x-app-layout>
