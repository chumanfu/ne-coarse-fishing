<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Tackle reviews</h1>
                <p class="text-slate-600 mt-1">Gear write-ups from North East anglers — rods, reels, baits and more.</p>
            </div>
            @auth
                <a href="{{ route('tackle-reviews.create') }}" class="inline-flex px-4 py-2 rounded-md bg-sky-700 text-white font-semibold text-sm">Write a review</a>
            @else
                <a href="{{ route('login') }}" class="inline-flex px-4 py-2 rounded-md border-2 border-slate-400 font-semibold text-sm">Log in to review</a>
            @endauth
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($reviews as $review)
                <a href="{{ route('tackle-reviews.show', $review) }}" class="block bg-white border-2 border-slate-300 rounded-xl overflow-hidden hover:border-sky-700 transition">
                    @if ($review->photos->isNotEmpty())
                        <img
                            src="{{ $review->photos->first()->url() }}"
                            alt="{{ $review->displayName() }} photo"
                            class="w-full h-40 object-cover border-b-2 border-slate-200 cursor-zoom-in"
                            role="button"
                            tabindex="0"
                            @click.prevent.stop="$store.photoLightbox.open(@js($review->photos->map(fn ($photo) => ['url' => $photo->url(), 'alt' => $review->displayName().' photo'])->values()->all()), 0, 'Review photo')"
                            @keydown.enter.prevent.stop="$store.photoLightbox.open(@js($review->photos->map(fn ($photo) => ['url' => $photo->url(), 'alt' => $review->displayName().' photo'])->values()->all()), 0, 'Review photo')"
                            @keydown.space.prevent.stop="$store.photoLightbox.open(@js($review->photos->map(fn ($photo) => ['url' => $photo->url(), 'alt' => $review->displayName().' photo'])->values()->all()), 0, 'Review photo')"
                        >
                    @endif
                    <div class="p-5">
                        <h2 class="font-bold text-lg text-slate-900">{{ $review->displayName() }}</h2>
                        <div class="mt-2"><x-star-rating :rating="$review->rating" /></div>
                        <p class="text-sm text-slate-700 mt-3 line-clamp-3">{{ $review->body }}</p>
                        <p class="text-xs text-slate-500 mt-3">By {{ $review->user->name }}</p>
                    </div>
                </a>
            @empty
                <p class="text-slate-600 sm:col-span-2 lg:col-span-3">No tackle reviews yet. Be the first to share what you’re using.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $reviews->links() }}</div>
    </div>
</x-app-layout>
