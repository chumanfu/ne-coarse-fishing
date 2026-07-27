@props(['venue'])

<a href="{{ route('venues.show', $venue) }}" {{ $attributes->merge(['class' => 'block bg-white border-2 border-slate-300 rounded-xl overflow-hidden hover:border-sky-700 transition']) }}>
    @if ($venue->relationLoaded('photos') && $venue->photos->isNotEmpty())
        <img src="{{ $venue->photos->first()->url() }}" alt="" class="w-full h-36 object-cover border-b-2 border-slate-200">
    @endif
    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="font-bold text-lg text-slate-900">{{ $venue->name }}</h3>
                @if ($venue->address)
                    <p class="text-sm text-slate-600 mt-1">{{ $venue->address }}</p>
                @endif
            </div>
            @if ($venue->manager_verified)
                <span class="shrink-0 text-xs font-bold uppercase tracking-wide bg-emerald-100 text-emerald-900 border border-emerald-600 px-2 py-1 rounded">Verified</span>
            @endif
        </div>
        <p class="text-sm text-slate-700 mt-3 line-clamp-3">{{ $venue->overview ?: 'Community-submitted venue awaiting a full write-up.' }}</p>
        <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
            <span class="bg-slate-100 border border-slate-300 px-2 py-1 rounded">{{ $venue->ticketTypeLabel() }}</span>
            @if ($venue->is_complex)
                <span class="bg-amber-50 border border-amber-500 text-amber-950 px-2 py-1 rounded">Complex</span>
            @endif
            @foreach ($venue->allSpecies()->take(4) as $species)
                <span class="bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded">{{ $species->name }}</span>
            @endforeach
        </div>
    </div>
</a>
