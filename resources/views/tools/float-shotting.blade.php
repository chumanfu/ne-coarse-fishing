@php
    $chipBase = 'inline-flex items-center gap-1.5 rounded-full border-2 px-3 py-1.5 text-sm font-semibold transition-colors min-h-9';
    $chipOn = 'border-moss bg-moss-soft text-moss-dark';
    $chipOff = 'border-slate-300 bg-white text-slate-600 hover:text-slate-900';
    $inputClass = 'w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700';

    $prefill = [
        'venues' => $venues,
        'venueId' => $peg?->water?->venue_id ?? '',
        'pegId' => $peg?->id ?? '',
    ];

    if ($rig) {
        $prefill += [
            'floatName' => $rig->float_name,
            'floatSize' => $rig->float_size,
            'floatType' => $rig->float_type,
            'depth' => $rig->depth,
            'depthUnit' => $rig->depth_unit,
            'patternId' => $rig->pattern_id,
            'olivetteGrams' => $rig->olivette_grams,
        ];
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900">Float shotting</h1>
        <p class="text-slate-600 mt-1">Enter the float size and the depth you are fishing, and the calculator works out which shot to use and where to put it.</p>
    </x-slot>

    <div
        class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4"
        x-data="shottingCalculator(@js($prefill))"
        x-effect="diagram && $nextTick(() => measureLabels())"
    >
        @if (session('status'))
            <p class="bg-moss-soft border-2 border-moss text-moss-dark font-semibold rounded-xl px-4 py-3" role="status">{{ session('status') }}</p>
        @endif

        @if ($peg)
            <p class="text-sm text-slate-600">
                Saving to <span class="font-semibold text-slate-900">{{ $peg->water->venue->name }} · {{ $peg->label() }}</span>.
                <a href="{{ route('venues.show', $peg->water->venue) }}" class="font-semibold text-sky-800 hover:underline">Back to the venue</a>
            </p>
        @endif

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <h2 class="text-lg font-bold text-slate-900 mb-3">Float</h2>

            <div class="mb-3">
                <label for="float-name" class="block text-sm font-semibold mb-1">Float name</label>
                <input id="float-name" type="text" x-model="floatName" autocapitalize="words"
                       placeholder="e.g. Preston Chianti" class="{{ $inputClass }}">
            </div>

            <div class="mb-3">
                <label for="float-size" class="block text-sm font-semibold mb-1">Size</label>
                <input id="float-size" type="text" x-model="floatSize" @input="onSizeChange()"
                       autocapitalize="none" autocorrect="off" spellcheck="false"
                       :aria-invalid="sizeInvalid" aria-describedby="float-size-hint"
                       placeholder="e.g. 4x10, 4x16, 0.5g, 3BB, 4No4" class="{{ $inputClass }}">
                <p id="float-size-hint" class="mt-1 text-xs" x-show="sizeHint" x-cloak
                   :class="sizeInvalid ? 'text-red-700' : 'text-slate-600'" x-text="sizeHint"></p>
            </div>

            <p class="text-sm font-semibold text-slate-700 mb-2">Float type</p>
            <div class="flex flex-wrap gap-2" role="group" aria-label="Float type">
                <template x-for="type in floatTypes" :key="type.id">
                    <button type="button" @click="selectType(type.id)" :aria-pressed="floatType === type.id"
                            :title="type.hint" x-text="type.label"
                            class="{{ $chipBase }}"
                            :class="floatType === type.id ? '{{ $chipOn }}' : '{{ $chipOff }}'"></button>
                </template>
            </div>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
            <h2 class="text-lg font-bold text-slate-900 mb-3">Depth</h2>
            <div class="flex items-start gap-3">
                <div class="flex-1">
                    <label for="depth" class="block text-sm font-semibold mb-1">
                        Depth to fish (<span x-text="unit === 'ft' ? 'feet' : 'metres'"></span>)
                    </label>
                    <input id="depth" type="text" inputmode="decimal" x-model="depthText"
                           :placeholder="unit === 'ft' ? 'e.g. 5' : 'e.g. 1.5'"
                           :aria-invalid="depthInvalid" aria-describedby="depth-hint" class="{{ $inputClass }}">
                    <p id="depth-hint" class="mt-1 text-xs text-red-700" x-show="depthInvalid" x-cloak>
                        Enter a depth between 1ft (0.3m) and 49ft (15m)
                    </p>
                </div>
                <div class="flex gap-1.5 mt-7" role="group" aria-label="Depth unit">
                    <button type="button" @click="unit = 'ft'" :aria-pressed="unit === 'ft'"
                            class="{{ $chipBase }}" :class="unit === 'ft' ? '{{ $chipOn }}' : '{{ $chipOff }}'">ft</button>
                    <button type="button" @click="unit = 'm'" :aria-pressed="unit === 'm'"
                            class="{{ $chipBase }}" :class="unit === 'm' ? '{{ $chipOn }}' : '{{ $chipOff }}'">m</button>
                </div>
            </div>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5" x-show="active" x-cloak>
            <h2 class="text-lg font-bold text-slate-900 mb-3">Shotting pattern</h2>

            <div class="flex flex-wrap gap-2 mb-4" role="group" aria-label="Pattern" x-show="patterns.length > 1">
                <template x-for="pattern in patterns" :key="pattern.id">
                    <button type="button" @click="patternId = pattern.id" :aria-pressed="pattern.id === active?.id"
                            class="{{ $chipBase }}"
                            :class="pattern.id === active?.id ? '{{ $chipOn }}' : '{{ $chipOff }}'">
                        <span x-text="pattern.name"></span>
                        <template x-if="pattern.recommended">
                            <span class="rounded-full bg-moss px-1.5 py-px text-[10px] font-extrabold text-white">BEST</span>
                        </template>
                    </button>
                </template>
            </div>

            <div class="rounded-xl bg-paper p-3 mb-4" x-show="active?.id === 'olivette'" x-cloak>
                <p class="text-sm font-semibold text-slate-700 mb-2">Olivette size</p>
                <div class="flex flex-wrap gap-2" role="group" aria-label="Olivette size">
                    <button type="button" @click="olivetteGrams = null" :aria-pressed="olivetteGrams === null"
                            class="{{ $chipBase }}" :class="olivetteGrams === null ? '{{ $chipOn }}' : '{{ $chipOff }}'">Auto</button>
                    <template x-for="option in olivetteChoices" :key="option">
                        <button type="button" @click="olivetteGrams = option"
                                :aria-pressed="activeOlivette === option && olivetteGrams !== null"
                                class="{{ $chipBase }}"
                                :class="activeOlivette === option && olivetteGrams !== null ? '{{ $chipOn }}' : '{{ $chipOff }}'">
                            <span x-text="formatGrams(option)"></span>
                            <template x-if="olivetteGrams === null && activeOlivette === option">
                                <span class="rounded-full bg-moss px-1.5 py-px text-[10px] font-extrabold text-white">AUTO</span>
                            </template>
                        </button>
                    </template>
                </div>
                <p class="text-sm text-slate-600 mt-2">The droppers adjust to whichever olivette you pick.</p>
            </div>

            <p class="text-slate-900" x-text="active?.summary"></p>
            <p class="text-sm text-slate-600 mt-1.5" x-text="active?.whenToUse"></p>

            {{-- Rig diagram: shot markers sit on the line, with labels nudged clear of each other. --}}
            <div class="relative mt-4 overflow-hidden rounded-xl bg-water-soft" x-show="diagram" x-cloak
                 :style="`height: ${diagram?.height}px`" role="img" :aria-label="diagram?.aria">
                <div class="absolute inset-x-0 h-px bg-water-mist" style="top: 30px"></div>
                <div class="absolute w-px bg-slate-400" :style="`left: ${lineX - 0.5}px; top: ${diagramTop}px; height: ${diagram?.lineLen}px`"></div>

                <div class="absolute rounded-t-[3px] bg-orange-500" :style="`top: 10px; left: ${lineX - 2.5}px; width: 5px; height: 20px`"></div>
                <div class="absolute rounded-full bg-paper-deep border border-bank/30" :style="`top: 28px; left: ${lineX - 7}px; width: 14px; height: 32px`"></div>
                <div class="absolute bg-paper-deep" :style="`top: 58px; left: ${lineX - 1.5}px; width: 3px; height: 14px`"></div>

                {{-- One straight leader per shot, so lines to displaced labels never merge. --}}
                <svg class="pointer-events-none absolute inset-0 h-full w-full text-water/40" aria-hidden="true">
                    <template x-for="(group, index) in diagram?.groups ?? []" :key="'leader-' + index">
                        <line :x1="group.leader.x1" :y1="group.leader.y1" :x2="group.leader.x2" :y2="group.leader.y2"
                              stroke="currentColor" stroke-width="1" />
                    </template>
                </svg>

                <template x-for="(group, index) in diagram?.groups ?? []" :key="'shot-' + index">
                    <div aria-hidden="true">
                        <div class="absolute border" :style="group.markerStyle"
                             :class="group.isLocking ? 'bg-water-dark border-water-dark' : 'bg-slate-400 border-slate-600'"></div>
                        <div class="absolute right-2.5 leading-tight" data-shot-label
                             :style="`top: ${group.labelTop}px; left: ${labelX}px`">
                            <div class="text-[13px] font-semibold text-slate-900" x-text="group.text"></div>
                            <div class="text-[11px] text-slate-600" x-text="group.position"></div>
                        </div>
                    </div>
                </template>

                <div class="absolute rounded-br-md border-r-2 border-b-2 border-slate-400"
                     :style="`top: ${diagram?.hookY - 4}px; left: ${lineX - 7}px; width: 8px; height: 14px`" aria-hidden="true"></div>
                <div class="absolute inset-x-0 h-0.5 bg-bank" :style="`top: ${diagram?.bedY}px`" aria-hidden="true"></div>
                <p class="absolute left-2.5 text-[11px] text-slate-600" :style="`top: ${diagram?.bedY + 6}px`">
                    Bottom · <span x-text="diagram?.depthLabel"></span> deep
                </p>
            </div>

            <table class="mt-4 w-full border-separate border-spacing-0 overflow-hidden rounded-xl border-2 border-slate-200 text-left">
                <caption class="sr-only">Shot positions</caption>
                <tbody>
                    <template x-for="(row, index) in rows" :key="'row-' + index">
                        <tr>
                            <th scope="row" class="w-20 p-2.5 align-middle text-xs font-bold text-sky-800"
                                :class="index > 0 && 'border-t-2 border-slate-200'" x-text="row.role"></th>
                            <td class="p-2.5" :class="index > 0 && 'border-t-2 border-slate-200'">
                                <div class="text-sm font-semibold text-slate-900" x-text="row.text"></div>
                                <div class="mt-0.5 text-xs text-slate-600"
                                     x-text="row.position + (row.note ? ' · ' + row.note : '')"></div>
                            </td>
                            <td class="p-2.5 text-right text-xs tabular-nums text-slate-600"
                                :class="index > 0 && 'border-t-2 border-slate-200'" x-text="row.grams"></td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="mt-3 rounded-xl bg-paper p-3">
                <p class="font-bold text-slate-900 mb-1" x-text="loadSummary?.heading"></p>
                <p class="text-sm text-slate-600" x-text="loadSummary?.detail"></p>
            </div>

            <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                <template x-for="(tip, index) in active?.tips ?? []" :key="'tip-' + index">
                    <li x-text="'• ' + tip"></li>
                </template>
            </ul>
        </section>

        <section class="bg-paper border-2 border-slate-300 rounded-xl p-5" x-show="! active" x-cloak>
            <p class="text-sm text-slate-700">Enter a float size and depth to see which shot to use and where to put it.</p>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5" x-show="active" x-cloak>
            <h2 class="text-lg font-bold text-slate-900 mb-3">Save to a peg</h2>

            @auth
                @if ($venues === [])
                    <p class="text-sm text-slate-600">No venues have verified pegs yet, so there is nowhere to save this pattern.</p>
                @else
                    <form method="POST" action="{{ route('tools.float-shotting.store') }}" class="space-y-3">
                        @csrf

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label for="save-venue" class="block text-sm font-semibold mb-1">Venue</label>
                                <select id="save-venue" x-model="venueId" @change="onVenueChange()" class="{{ $inputClass }}">
                                    <option value="">Choose a venue</option>
                                    @foreach ($venues as $venue)
                                        <option value="{{ $venue['id'] }}">{{ $venue['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="save-peg" class="block text-sm font-semibold mb-1">Peg</label>
                                <select id="save-peg" name="water_peg_id" x-model="pegId" required class="{{ $inputClass }}">
                                    <option value="" x-text="venueId ? 'Choose a peg' : 'Pick a venue first'"></option>
                                    <template x-for="peg in pegOptions" :key="peg.id">
                                        <option :value="peg.id" x-text="peg.label"></option>
                                    </template>
                                </select>
                                @error('water_peg_id')
                                    <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="rig-notes" class="block text-sm font-semibold mb-1">Notes (optional)</label>
                            <textarea id="rig-notes" name="notes" rows="2" maxlength="2000"
                                      placeholder="e.g. Fish came 6in off bottom" class="{{ $inputClass }}">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <input type="hidden" name="float_name" :value="savePayload.float_name">
                        <input type="hidden" name="float_size" :value="savePayload.float_size">
                        <input type="hidden" name="float_type" :value="savePayload.float_type">
                        <input type="hidden" name="float_grams" :value="savePayload.float_grams">
                        <input type="hidden" name="depth" :value="savePayload.depth">
                        <input type="hidden" name="depth_unit" :value="savePayload.depth_unit">
                        <input type="hidden" name="pattern_id" :value="savePayload.pattern_id">
                        <input type="hidden" name="olivette_grams" :value="savePayload.olivette_grams">

                        <button type="submit" class="px-5 py-3 rounded-md bg-sky-800 text-white font-bold hover:bg-sky-900">
                            Save shotting pattern
                        </button>
                    </form>
                @endif
            @else
                <p class="text-sm text-slate-700">
                    <a href="{{ route('register') }}" class="font-semibold text-sky-800 hover:underline">Create a free account</a>
                    or <a href="{{ route('login') }}" class="font-semibold text-sky-800 hover:underline">log in</a>
                    to save patterns against the pegs you fish.
                </p>
            @endauth
        </section>

        <section class="bg-paper border-2 border-slate-300 rounded-xl p-5">
            <h2 class="text-lg font-bold text-slate-900 mb-2">Before you fish</h2>
            <ul class="space-y-1 text-sm text-slate-700">
                <template x-for="(tip, index) in generalTips" :key="'general-' + index">
                    <li x-text="'• ' + tip"></li>
                </template>
            </ul>
            <p class="mt-2 text-sm text-slate-700" x-show="parsed" x-cloak>
                Float load used: <span x-text="formatGrams(parsed?.grams ?? 0)"></span>.
            </p>
            <p class="mt-2 text-sm text-slate-700">
                New to shotting patterns? Read the
                <a href="{{ route('tools.shot-guide') }}" class="font-semibold text-sky-800 hover:underline">shot guide</a>
                for shot weights, pole float sizes and what each pattern is for.
            </p>
        </section>
    </div>
</x-app-layout>
