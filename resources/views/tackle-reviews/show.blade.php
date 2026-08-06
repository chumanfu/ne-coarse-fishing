<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $review->displayName() }}</h1>
                <div class="mt-2"><x-star-rating :rating="$review->rating" /></div>
                <p class="text-slate-600 mt-2 text-sm">Reviewed by {{ $review->user->name }} · {{ $review->created_at->format('d M Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($review->purchase_url)
                    <a href="{{ $review->purchase_url }}" target="_blank" rel="noopener noreferrer" class="px-3 py-2 rounded-md border-2 border-slate-800 font-semibold text-sm">Buy / product link</a>
                @endif
                @auth
                    @if (auth()->id() === $review->user_id || auth()->user()->hasRole('super_admin'))
                        <a href="{{ route('tackle-reviews.edit', $review) }}" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm">Edit</a>
                        <form method="POST" action="{{ route('tackle-reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-2 rounded-md border-2 border-red-600 text-red-800 font-semibold text-sm">Delete</button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <p class="text-slate-800 whitespace-pre-line">{{ $review->body }}</p>
        </section>

        @if ($review->photos->isNotEmpty())
            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="font-bold text-lg mb-3">Photos</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($review->photos as $photo)
                        <a href="{{ $photo->url() }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ $photo->url() }}" alt="" class="rounded-lg object-cover h-40 w-full border border-slate-300">
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <x-share-links
            :url="route('tackle-reviews.show', $review)"
            :title="$review->displayName()"
            :text="'Tackle review: '.$review->displayName().' — '.$review->rating.'/5 on NE Coarse Fishing'"
        />

        <a href="{{ route('tackle-reviews.index') }}" class="inline-flex font-semibold text-sky-800 hover:underline">Back to reviews</a>
    </div>
</x-app-layout>
