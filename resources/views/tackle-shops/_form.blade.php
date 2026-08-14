@php
    $withLogo = $withLogo ?? false;
    $withMessage = $withMessage ?? false;
@endphp

<div>
    <label for="name" class="block text-sm font-semibold mb-1">Name</label>
    <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name', $shop->name) }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
    @error('name') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
</div>
<div>
    <label for="url" class="block text-sm font-semibold mb-1">Website URL</label>
    <input id="url" name="url" type="url" required maxlength="255" value="{{ old('url', $shop->url) }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
    @error('url') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
</div>
<div>
    <label for="location_type" class="block text-sm font-semibold mb-1">Type</label>
    <select id="location_type" name="location_type" required class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
        @foreach (\App\Models\TackleShop::LOCATION_TYPES as $value => $label)
            <option value="{{ $value }}" @selected(old('location_type', $shop->location_type) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('location_type') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
</div>
<div>
    <label for="town" class="block text-sm font-semibold mb-1">Town</label>
    <input id="town" name="town" type="text" maxlength="255" value="{{ old('town', $shop->town) }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
    @error('town') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
</div>
<div>
    <label for="address" class="block text-sm font-semibold mb-1">Address</label>
    <input id="address" name="address" type="text" maxlength="255" value="{{ old('address', $shop->address) }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
    @error('address') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="latitude" class="block text-sm font-semibold mb-1">Latitude</label>
        <input id="latitude" name="latitude" type="number" step="any" value="{{ old('latitude', $shop->latitude) }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
        @error('latitude') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="longitude" class="block text-sm font-semibold mb-1">Longitude</label>
        <input id="longitude" name="longitude" type="number" step="any" value="{{ old('longitude', $shop->longitude) }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
        @error('longitude') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
    </div>
</div>
<div>
    <label for="phone" class="block text-sm font-semibold mb-1">Phone</label>
    <input id="phone" name="phone" type="text" maxlength="50" value="{{ old('phone', $shop->phone) }}" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
    @error('phone') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
</div>
<div>
    <label for="overview" class="block text-sm font-semibold mb-1">Overview</label>
    <textarea id="overview" name="overview" rows="6" maxlength="5000" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('overview', $shop->overview) }}</textarea>
    @error('overview') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
</div>
@if ($withLogo)
    <div>
        <label for="logo" class="block text-sm font-semibold mb-1">Logo</label>
        @if ($shop->logoUrl())
            <button
                type="button"
                class="block mb-2 cursor-zoom-in"
                @click="$store.photoLightbox.open(@js([['url' => $shop->logoUrl(), 'alt' => $shop->name.' logo']]), 0, @js($shop->name.' logo'))"
            >
                <img src="{{ $shop->logoUrl() }}" alt="{{ $shop->name }} logo" class="h-16 w-16 object-contain border border-slate-300 rounded bg-white p-1">
            </button>
        @endif
        <input id="logo" name="logo" type="file" accept="image/*" class="block w-full text-sm">
        <p class="text-xs text-slate-500 mt-1">Uploads are stored on the site uploads disk (S3 in production).</p>
        @error('logo') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
    </div>
@endif
@if ($withMessage)
    <div>
        <label for="message" class="block text-sm font-semibold mb-1">Note for admins (optional)</label>
        <textarea id="message" name="message" rows="3" maxlength="2000" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('message') }}</textarea>
        @error('message') <p class="text-sm text-red-700 mt-1">{{ $message }}</p> @enderror
    </div>
@endif
