@php
    use App\Support\ShotReference;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Shot guide</h1>
        <p class="text-slate-600 mt-1">Shot weights, pole float sizes, olivettes and what each shotting pattern is for.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4">
        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <h2 class="text-lg font-bold text-slate-900 mb-1">Shot sizes</h2>
            <p class="text-sm text-slate-600 mb-3">Approximate weights — they vary slightly between brands.</p>
            <table class="w-full text-left">
                <thead class="sr-only">
                    <tr>
                        <th>Size</th>
                        <th>Weight</th>
                        <th>Used for</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (ShotReference::shots() as $index => $shot)
                        <tr @class(['border-t-2 border-slate-200' => $index > 0])>
                            <th scope="row" class="w-14 py-2.5 align-top text-sm font-bold text-sky-800">{{ $shot['label'] }}</th>
                            <td class="w-16 py-2.5 align-top text-sm tabular-nums">{{ ShotReference::formatGrams($shot['grams']) }}</td>
                            <td class="py-2.5 align-top text-[13px] leading-snug text-slate-600">{{ $shot['use'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <h2 class="text-lg font-bold text-slate-900 mb-1">Pole float sizes</h2>
            <p class="text-sm text-slate-600 mb-3">The second number is roughly hundredths of a gram. Always check the packaging, as brands differ.</p>
            <div class="flex flex-wrap gap-2">
                @foreach (ShotReference::poleSizes() as $size)
                    <div class="min-w-[84px] rounded-xl bg-paper px-3 py-2">
                        <p class="font-bold text-slate-900">{{ $size }}</p>
                        <p class="text-sm text-slate-600">≈ {{ ShotReference::formatGrams(((int) explode('x', $size)[1]) / 100) }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <h2 class="text-lg font-bold text-slate-900 mb-1">Olivettes</h2>
            <p class="text-sm text-slate-600 mb-3">
                Streamlined in-line pole weights used instead of a bulk. Pick one that takes about 80–90% of the float's
                rating and make up the rest with droppers.
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach (ShotReference::olivettes() as $grams)
                    <div class="min-w-[84px] rounded-xl bg-paper px-3 py-2">
                        <p class="font-bold text-slate-900">{{ ShotReference::formatGrams($grams) }}</p>
                        <p class="text-sm text-slate-600">float ≈ {{ ShotReference::formatGrams($grams / 0.85) }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <h2 class="text-lg font-bold text-slate-900 mb-3">Shotting patterns</h2>
            @foreach (ShotReference::patterns() as $pattern)
                <div class="mb-3 last:mb-0">
                    <h3 class="font-bold text-slate-900 mb-0.5">{{ $pattern['name'] }}</h3>
                    <p class="text-sm text-slate-600">{{ $pattern['text'] }}</p>
                </div>
            @endforeach
        </section>

        <p class="text-sm text-slate-600">
            Ready to work out a rig?
            <a href="{{ route('tools.float-shotting') }}" class="font-semibold text-sky-800 hover:underline">Open the float shotting calculator</a>.
        </p>
    </div>
</x-app-layout>
