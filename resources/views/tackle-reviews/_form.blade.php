@php
    $review = $review ?? null;
@endphp

<div>
    <label class="block text-sm font-semibold mb-1">Product name</label>
    <input name="title" value="{{ old('title', $review->title ?? '') }}" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="e.g. Method Feeder">
    @error('title') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-semibold mb-1">Brand (optional)</label>
    <input name="brand" value="{{ old('brand', $review->brand ?? '') }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="e.g. Guru">
    @error('brand') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-semibold mb-1">Rating (0–5 stars)</label>
    <select name="rating" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
        @for ($i = 0; $i <= 5; $i++)
            <option value="{{ $i }}" @selected((int) old('rating', $review->rating ?? 5) === $i)>{{ $i }} {{ $i === 1 ? 'star' : 'stars' }}</option>
        @endfor
    </select>
    @error('rating') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-semibold mb-1">Write-up</label>
    <textarea name="body" rows="8" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="How it performs on the bank, who it’s for, and anything you’d change.">{{ old('body', $review->body ?? '') }}</textarea>
    @error('body') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-semibold mb-1">Where you bought it (optional URL)</label>
    <input type="url" name="purchase_url" value="{{ old('purchase_url', $review->purchase_url ?? '') }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700" placeholder="https://">
    @error('purchase_url') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
</div>

@if ($review && $review->photos->isNotEmpty())
    <div>
        <label class="block text-sm font-semibold mb-2">Existing photos</label>
        @php
            $reviewPhotoGallery = $review->photos->map(fn ($item) => [
                'url' => $item->url(),
                'alt' => 'Review photo',
            ])->values()->all();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach ($review->photos as $photo)
                <div class="relative rounded-lg border-2 border-slate-300 overflow-hidden">
                    <button
                        type="button"
                        class="block w-full text-left"
                        @click="$store.photoLightbox.open(@js($reviewPhotoGallery), {{ $loop->index }}, 'Review photo')"
                    >
                        <img src="{{ $photo->url() }}" alt="" class="h-28 w-full object-cover hover:opacity-95">
                    </button>
                    <label class="absolute inset-x-0 bottom-0 bg-slate-900/75 text-white text-xs font-semibold px-2 py-1 flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remove_photo_ids[]" value="{{ $photo->id }}"> Remove
                    </label>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div>
    <label class="block text-sm font-semibold mb-1">Photos (up to 6)</label>
    <input type="file" name="photos[]" accept="image/*" multiple class="block w-full text-sm">
    @error('photos') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
    @error('photos.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
</div>
