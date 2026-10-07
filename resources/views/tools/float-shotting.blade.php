@php
    $chipBase = 'inline-flex items-center gap-1.5 rounded-full border-2 px-3 py-1.5 text-sm font-semibold transition-colors min-h-9';
    $chipOn = 'border-moss bg-moss-soft text-moss-dark';
    $chipOff = 'border-slate-300 bg-white text-slate-600 hover:text-slate-900';
    $inputClass = 'w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700';

    $venueIds = old('venue_ids', $rig ? $rig->venues->pluck('id')->all() : ($peg?->water?->venue_id ? [$peg->water->venue_id] : []));
    $pegIds = old('peg_ids', $rig ? $rig->pegs->pluck('id')->all() : ($peg ? [$peg->id] : []));

    $prefill = [
        'venues' => $venues,
        'venueIds' => array_values($venueIds),
        'pegIds' => array_values($pegIds),
        'rigName' => old('name', $rig->name ?? ''),
        'placements' => $placements,
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
        @if ($rig?->is_system)
            <h1 class="text-2xl font-bold text-slate-900">{{ $rig->displayName() }}</h1>
            <p class="text-slate-600 mt-1">A system rig. Duplicate it to make your own copy. This one stays as it is.</p>
        @else
            <h1 class="text-2xl font-bold text-slate-900">{{ $canUpdate ? 'Edit rig' : 'New rig' }}</h1>
            <p class="text-slate-600 mt-1">Work out the shot a float needs, then move the bulk and the droppers. Save the rig when the diagram looks right.</p>
        @endif
    </x-slot>

    <div
        class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-4"
        x-data="shottingCalculator(@js($prefill))"
        x-effect="diagram && $nextTick(() => measureLabels())"
    >
        @if (session('status'))
            <p class="bg-moss-soft border-2 border-moss text-moss-dark font-semibold rounded-xl px-4 py-3" role="status">{{ session('status') }}</p>
        @endif

        <p class="text-sm text-slate-600">
            <a href="{{ route('tools.rigs') }}" class="font-semibold text-sky-800 hover:underline">All rigs</a>
            @if ($peg)
                <span class="text-slate-400">·</span>
                Starting from <span class="font-semibold text-slate-900">{{ $peg->water->venue->name }} · {{ $peg->label() }}</span>
            @endif
        </p>

        <fieldset @disabled($rig?->is_system) class="min-w-0 space-y-4 border-0 p-0 disabled:opacity-100">
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
                       placeholder="e.g. 4x10, 0.5g, 3BB, 1+2BB, 1.5g + 0.5g" class="{{ $inputClass }}">
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
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-slate-900">Shot pattern</h2>
                <button type="button" @click="resetShots()" :disabled="! canResetShots"
                        class="{{ $chipBase }} border-slate-300 bg-white text-slate-900 hover:text-slate-900 disabled:opacity-50">
                    Reset shots
                </button>
            </div>

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

            <div class="rounded-xl bg-paper p-3 mb-4" x-show="usesOlivette" x-cloak>
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

            @if ($rig?->is_system)
                <p class="text-slate-900">{{ $rig->notes }}</p>
            @else
                <p class="text-slate-900" x-text="active?.summary"></p>
                <p class="text-sm text-slate-600 mt-1.5" x-text="active?.whenToUse"></p>
            @endif

            <div class="mt-4 rounded-xl bg-paper p-3">
                <h3 class="text-sm font-bold text-slate-900">Dot the tip</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Try a combination until only the
                    <span x-text="floatType === 'pole' || floatType === 'dibber' ? 'bristle' : 'coloured tip'"></span>
                    is above the water. Each shot you add sinks the float.
                </p>

                <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Shot combinations">
                    <template x-for="choice in dotChoices" :key="choice.id">
                        <button type="button" @click="applyDots(choice)" :aria-pressed="dotPick === choice.id"
                                class="{{ $chipBase }}"
                                :class="dotPick === choice.id ? '{{ $chipOn }}' : '{{ $chipOff }}'">
                            <span x-text="choice.label"></span>
                        </button>
                    </template>
                </div>

                <ul class="mt-3 space-y-2" x-show="dots.length" x-cloak>
                    <template x-for="(size, index) in dots" :key="index + '-' + size">
                        <li class="flex flex-wrap items-center gap-2">
                            <label class="sr-only" :for="'dot-size-' + index">Shot <span x-text="index + 1"></span> size</label>
                            <select :id="'dot-size-' + index" class="rounded-md border-2 border-slate-400 py-1.5 pl-2 pr-8 text-sm"
                                    :value="size" @change="changeDot(index, $event.target.value)">
                                <template x-for="option in dotSizeOptions" :key="option.size">
                                    <option :value="option.size" x-text="option.label" :selected="option.size === size"></option>
                                </template>
                            </select>
                            <button type="button" @click="removeDot(index)"
                                    class="text-sm font-semibold text-sky-800 hover:underline">Remove</button>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="mt-4 rounded-xl bg-paper p-3">
                <h3 class="text-sm font-bold text-slate-900">Add a shot</h3>
                <p class="mt-1 text-sm text-slate-600">Choose a size, then add it to the locking shot or the bulk, or put it on the line as a dropper or a trimmer.</p>
                <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Shot size to add">
                    <template x-for="size in shotSizes" :key="'add-' + size.size">
                        <button type="button" @click="addSize = size.size" :aria-pressed="addSize === size.size"
                                class="{{ $chipBase }}"
                                :class="addSize === size.size ? '{{ $chipOn }}' : '{{ $chipOff }}'"
                                x-text="size.label"></button>
                    </template>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" @click="addToLocking()" :disabled="! canAddToLocking"
                            class="{{ $chipBase }} border-slate-300 bg-white text-slate-900 hover:text-slate-900 disabled:opacity-50">
                        Add to locking
                    </button>
                    <button type="button" @click="addToBulk()" :disabled="! canAddToBulk"
                            class="{{ $chipBase }} border-slate-300 bg-white text-slate-900 hover:text-slate-900 disabled:opacity-50">
                        Add to bulk
                    </button>
                    <button type="button" @click="addPlacedShot('dropper')" :disabled="! canAddLineShot"
                            class="{{ $chipBase }} border-slate-300 bg-white text-slate-900 hover:text-slate-900 disabled:opacity-50">
                        Add dropper
                    </button>
                    <button type="button" @click="addPlacedShot('trim')" :disabled="! canAddLineShot"
                            class="{{ $chipBase }} border-slate-300 bg-white text-slate-900 hover:text-slate-900 disabled:opacity-50">
                        Add trimmer
                    </button>
                </div>
            </div>

            <div class="mt-4 rounded-xl bg-paper p-3" x-show="loadSummary" x-cloak>
                <p class="font-bold text-slate-900 mb-1" x-text="loadSummary?.heading"></p>
                <p class="text-sm text-slate-600" x-text="loadSummary?.detail"></p>
            </div>

            {{-- Rig diagram: the float sinks through the water line as dotting shot is added. --}}
            <div class="relative mt-4 overflow-hidden rounded-xl bg-water-soft" x-show="diagram" x-cloak
                 :style="`height: ${diagram?.height}px`" role="img" :aria-label="diagram?.aria">
                <div class="absolute inset-x-0 bg-paper" :style="`height: ${diagram?.waterY}px`"></div>
                <div class="absolute inset-x-0 h-0.5 bg-water" :style="`top: ${diagram?.waterY}px`"></div>
                <p class="absolute right-2.5 text-[11px] font-semibold text-slate-900" style="top: 8px" x-text="diagram?.sitTitle"></p>
                <p class="absolute right-2.5 text-[11px] font-semibold text-water-dark" :style="`top: ${diagram?.waterY - 16}px`">Water</p>

                <div class="absolute w-px bg-slate-400" :style="`left: ${lineX - 0.5}px; top: ${diagram?.lineTop}px; height: ${diagram?.lineLen}px`"></div>

                {{-- The sight tip sits above the water; the body is the part that belongs to that float. --}}
                <div class="pointer-events-none absolute z-10 transition-[top] duration-200"
                     :style="`top: ${diagram?.floatTop ?? 0}px; left: ${lineX - ((diagram?.picture?.w ?? 24) / 2)}px; width: ${diagram?.picture?.w ?? 24}px; height: ${diagram?.picture?.h ?? 66}px`">
                    <svg x-show="diagram?.kind === 'pole'" x-cloak viewBox="0 0 16 44" class="h-full w-full overflow-visible" aria-hidden="true">
                        <rect x="7" y="0" width="2" height="20" rx="1" fill="#f97316"/>
                        <ellipse cx="8" cy="26" rx="5.5" ry="8" fill="#fcfaf5" stroke="#4a3728" stroke-width="1.2"/>
                        <circle cx="8" cy="24" r="1.3" fill="#f97316"/>
                        <rect x="7.2" y="33.5" width="1.6" height="10" rx="0.4" fill="#5c4a3a"/>
                    </svg>
                    <svg x-show="diagram?.kind === 'dibber'" x-cloak viewBox="0 0 22 30" class="h-full w-full overflow-visible" aria-hidden="true">
                        <rect x="9" y="0" width="4" height="12" rx="1.5" fill="#f97316"/>
                        <ellipse cx="11" cy="17" rx="9" ry="8" fill="#f0d9a8" stroke="#4a3728" stroke-width="1.2"/>
                        <rect x="10" y="24.5" width="2" height="5" rx="0.4" fill="#5c4a3a"/>
                    </svg>
                    <svg x-show="diagram?.kind === 'waggler'" x-cloak viewBox="0 0 14 52" class="h-full w-full overflow-visible" aria-hidden="true">
                        <rect x="5" y="0" width="4" height="9" rx="1" fill="#f97316"/>
                        <rect x="5" y="8" width="4" height="4" fill="#f5c542"/>
                        <rect x="5.2" y="12" width="3.6" height="4" fill="#fcfaf5"/>
                        <polygon points="4.6,15 9.4,15 10.4,45 3.6,45" fill="#e7d3a4" stroke="#a68558" stroke-width="0.6" stroke-linejoin="round"/>
                        <rect x="3.2" y="43" width="7.6" height="8" rx="2" fill="#c17a32" stroke="#8a7360" stroke-width="0.6"/>
                    </svg>
                    <svg x-show="diagram?.kind === 'loaded_waggler'" x-cloak viewBox="0 0 16 54" class="h-full w-full overflow-visible" aria-hidden="true">
                        <rect x="6" y="0" width="4" height="9" rx="1" fill="#f97316"/>
                        <rect x="6" y="8" width="4" height="4" fill="#f5c542"/>
                        <rect x="6.2" y="12" width="3.6" height="3" fill="#fcfaf5"/>
                        <polygon points="5.5,14 10.5,14 11,40 5,40" fill="#e7d3a4" stroke="#a68558" stroke-width="0.6" stroke-linejoin="round"/>
                        <ellipse cx="8" cy="46" rx="5.5" ry="7" fill="#3d474f"/>
                        <ellipse cx="6.6" cy="43.5" rx="1.6" ry="1" fill="#7d8b94"/>
                    </svg>
                    <svg x-show="diagram?.kind === 'pellet_waggler'" x-cloak viewBox="0 0 20 36" class="h-full w-full overflow-visible" aria-hidden="true">
                        <rect x="8" y="0" width="4" height="8" rx="1" fill="#f97316"/>
                        <rect x="8" y="7" width="4" height="3" fill="#f5c542"/>
                        <path d="M10 9 C4 13 3 22 10 33 C17 22 16 13 10 9 Z" fill="#f0d9a8" stroke="#4a3728" stroke-width="1.2" stroke-linejoin="round"/>
                        <rect x="9.2" y="32" width="1.6" height="4" fill="#5c4a3a"/>
                    </svg>
                    <svg x-show="diagram?.kind === 'slider'" x-cloak viewBox="0 0 18 64" class="h-full w-full overflow-visible" aria-hidden="true">
                        <rect x="7" y="0" width="4" height="11" rx="1" fill="#f97316"/>
                        <rect x="7" y="10" width="4" height="4" fill="#f5c542"/>
                        <rect x="7.2" y="14" width="3.6" height="4" fill="#fcfaf5"/>
                        <ellipse cx="9" cy="30" rx="6.5" ry="9" fill="#f0d9a8" stroke="#4a3728" stroke-width="1.2"/>
                        <polygon points="7.2,38 10.8,38 12,54 6,54" fill="#c17a32" stroke="#8a7360" stroke-width="0.6" stroke-linejoin="round"/>
                        <rect x="5" y="52" width="8" height="10" rx="2.5" fill="#a68558" stroke="#6b5746" stroke-width="0.6"/>
                        <circle cx="9" cy="57" r="2" fill="#e7f1f4" stroke="#356575" stroke-width="1"/>
                    </svg>
                    <svg x-show="diagram?.kind === 'stick'" x-cloak viewBox="0 0 16 50" class="h-full w-full overflow-visible" aria-hidden="true">
                        <path d="M5 10 Q8 1 11 10 Z" fill="#e85d04"/>
                        <path d="M4.2 10 Q3 18 5.2 24 L10.8 24 Q13 18 11.8 10 Z" fill="#f0d9a8" stroke="#6b5746" stroke-width="0.7" stroke-linejoin="round"/>
                        <polygon points="5.2,23.5 10.8,23.5 9.2,42 6.8,42" fill="#c17a32" stroke="#8a7360" stroke-width="0.6" stroke-linejoin="round"/>
                        <rect x="7.2" y="41.5" width="1.6" height="8" fill="#4a433c"/>
                    </svg>
                    <svg x-show="diagram?.kind === 'avon'" x-cloak viewBox="0 0 20 52" class="h-full w-full overflow-visible" aria-hidden="true">
                        <path d="M7 10 Q10 1 13 10 Z" fill="#e85d04"/>
                        <polygon points="8,10 12,10 11.2,18 8.8,18" fill="#c17a32" stroke="#8a7360" stroke-width="0.5" stroke-linejoin="round"/>
                        <ellipse cx="10" cy="26" rx="7" ry="8" fill="#f0d9a8" stroke="#4a3728" stroke-width="1.2"/>
                        <polygon points="8.6,33.5 11.4,33.5 10.4,44 9.6,44" fill="#c17a32" stroke="#8a7360" stroke-width="0.5" stroke-linejoin="round"/>
                        <rect x="9.2" y="43.5" width="1.6" height="8" fill="#4a433c"/>
                    </svg>
                </div>
                <div class="absolute z-10 w-px bg-slate-400 transition-[top] duration-200"
                     :style="`top: ${diagram?.stemTop ?? 0}px; left: ${lineX - 0.5}px; height: ${diagram?.stemH ?? 0}px`"></div>

                {{-- One straight leader per shot, so lines to displaced labels never merge. --}}
                <svg class="pointer-events-none absolute inset-0 h-full w-full text-water/40" aria-hidden="true">
                    <template x-for="(group, index) in diagram?.groups ?? []" :key="'leader-' + index">
                        <line :x1="group.leader.x1" :y1="group.leader.y1" :x2="group.leader.x2" :y2="group.leader.y2"
                              stroke="currentColor" stroke-width="1" />
                    </template>
                </svg>

                <template x-for="(group, index) in diagram?.groups ?? []" :key="'shot-' + index">
                    <div aria-hidden="true">
                        <div class="absolute z-30 border" :style="group.markerStyle"
                             :class="group.isStops ? 'bg-water border-water' : (group.isLocking ? 'bg-water-dark border-water-dark' : 'bg-slate-400 border-slate-600')"></div>
                        <div class="absolute z-30 right-2.5 leading-tight" data-shot-label
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

            <p class="mt-4 text-sm text-slate-600">Change a shot, take it off, or add to the bulk and reduce it with the count. Set how far the bulk and each dropper sit from the hook or the float, in centimetres or inches. The diagram follows.</p>

            <table class="mt-3 w-full border-separate border-spacing-0 overflow-hidden rounded-xl border-2 border-slate-200 text-left">
                <caption class="sr-only">Shot positions</caption>
                <tbody>
                    <template x-for="(row, index) in rows" :key="row.key">
                        <tr>
                            <th scope="row" class="w-20 p-2.5 align-top text-xs font-bold text-sky-800"
                                :class="index > 0 && 'border-t-2 border-slate-200'" x-text="row.role"></th>
                            <td class="p-2.5" :class="index > 0 && 'border-t-2 border-slate-200'">
                                <template x-if="! row.editable">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900" x-text="row.text"></div>
                                        <div class="mt-0.5 text-xs text-slate-600"
                                             x-text="row.position + (row.note ? ' · ' + row.note : '')"></div>
                                    </div>
                                </template>
                                <template x-if="row.editable">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900" x-show="row.olivetteLabel" x-text="row.olivetteLabel"></p>
                                        <div class="mt-0.5 text-xs text-slate-600"
                                             x-text="row.position + (row.note ? ' · ' + row.note : '')"></div>
                                        <div class="mt-2 space-y-2">
                                            <template x-for="item in row.items" :key="row.key + '-' + item.itemIndex + '-' + item.size + '-' + item.count">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <label class="sr-only" :for="'count-' + row.key + '-' + item.itemIndex">Number of shot</label>
                                                    <select :id="'count-' + row.key + '-' + item.itemIndex"
                                                            class="rounded-md border-2 border-slate-400 py-1 pl-2 pr-7 text-sm"
                                                            :value="item.count"
                                                            @change="changeShotCount(row.key, item.itemIndex, $event.target.value)">
                                                        <template x-for="count in shotCounts" :key="count">
                                                            <option :value="count" x-text="count" :selected="count === item.count"></option>
                                                        </template>
                                                    </select>
                                                    <span class="text-sm text-slate-600">×</span>
                                                    <label class="sr-only" :for="'size-' + row.key + '-' + item.itemIndex">Shot size</label>
                                                    <select :id="'size-' + row.key + '-' + item.itemIndex"
                                                            class="rounded-md border-2 border-slate-400 py-1 pl-2 pr-8 text-sm"
                                                            :value="item.size"
                                                            @change="changeShot(row.key, item.itemIndex, $event.target.value)">
                                                        <template x-for="option in shotSizes" :key="option.size">
                                                            <option :value="option.size" x-text="option.label" :selected="option.size === item.size"></option>
                                                        </template>
                                                    </select>
                                                    <button type="button" @click="removeShot(row.key, item.itemIndex)"
                                                            class="text-sm font-semibold text-sky-800 hover:underline">Remove</button>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="mt-2 flex flex-wrap items-center gap-2" x-show="row.canPlace">
                                            <label class="text-xs font-semibold text-slate-700" :for="'distance-' + row.key">Distance</label>
                                            <input :id="'distance-' + row.key" type="number" min="0" step="0.1" inputmode="decimal"
                                                   class="w-24 rounded-md border-2 border-slate-400 py-1 px-2 text-sm"
                                                   :value="row.distance"
                                                   @change="setPosition(row.key, { distance: $event.target.value })">
                                            <label class="sr-only" :for="'unit-' + row.key">Distance unit</label>
                                            <select :id="'unit-' + row.key" class="rounded-md border-2 border-slate-400 py-1 pl-2 pr-7 text-sm"
                                                    :value="row.distanceUnit"
                                                    @change="setPosition(row.key, { unit: $event.target.value })">
                                                <option value="cm" :selected="row.distanceUnit === 'cm'">cm</option>
                                                <option value="in" :selected="row.distanceUnit === 'in'">in</option>
                                            </select>
                                            <span class="text-sm text-slate-600">from the</span>
                                            <label class="sr-only" :for="'anchor-' + row.key">Measured from</label>
                                            <select :id="'anchor-' + row.key" class="rounded-md border-2 border-slate-400 py-1 pl-2 pr-7 text-sm"
                                                    :value="row.anchor"
                                                    @change="setPosition(row.key, { anchor: $event.target.value })">
                                                <option value="hook" :selected="row.anchor === 'hook'">hook</option>
                                                <option value="float" :selected="row.anchor === 'float'">float</option>
                                            </select>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <button type="button" x-show="row.canAddShot" @click="addShot(row.key)"
                                                    class="text-sm font-semibold text-sky-800 hover:underline">Add shot</button>
                                            <button type="button" @click="moveShot(row.key, 'float')" :disabled="! row.canMoveUp"
                                                    class="text-sm font-semibold text-sky-800 hover:underline disabled:text-slate-400 disabled:no-underline">Towards float</button>
                                            <button type="button" @click="moveShot(row.key, 'hook')" :disabled="! row.canMoveDown"
                                                    class="text-sm font-semibold text-sky-800 hover:underline disabled:text-slate-400 disabled:no-underline">Towards hook</button>
                                            <button type="button" x-show="row.olivetteLabel" @click="removePlacement(row.key)"
                                                    class="text-sm font-semibold text-sky-800 hover:underline">Remove olivette</button>
                                        </div>
                                    </div>
                                </template>
                            </td>
                            <td class="p-2.5 text-right align-top text-xs tabular-nums text-slate-600"
                                :class="index > 0 && 'border-t-2 border-slate-200'" x-text="row.grams"></td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                <template x-for="(tip, index) in active?.tips ?? []" :key="'tip-' + index">
                    <li x-text="'• ' + tip"></li>
                </template>
            </ul>
        </section>
        </fieldset>

        <section class="bg-paper border-2 border-slate-300 rounded-xl p-5" x-show="! active" x-cloak>
            <p class="text-sm text-slate-700">Enter a float size and depth to see which shot to use and where to put it.</p>
        </section>

        <section class="bg-white border-2 border-slate-300 rounded-xl p-5" x-show="active" x-cloak>
            @if ($rig?->is_system)
                <h2 class="text-lg font-bold text-slate-900 mb-3">Duplicate this rig</h2>
                <p class="text-sm text-slate-700 mb-4">Duplicating makes a copy that belongs to you. Change the shot on that copy. This system rig cannot be edited or deleted.</p>
                @auth
                    <form method="POST" action="{{ route('tools.rigs.duplicate', $rig) }}" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="grow">
                            <label for="rig-name" class="block text-sm font-semibold mb-1">Name for your copy</label>
                            <input id="rig-name" name="name" type="text" required maxlength="120"
                                   value="{{ mb_substr('Copy of '.$rig->displayName(), 0, 120) }}" class="{{ $inputClass }}">
                        </div>
                        <button type="submit" class="px-5 py-3 rounded-md bg-sky-800 text-white font-bold hover:bg-sky-900">
                            Duplicate
                        </button>
                    </form>
                @else
                    <p class="text-sm text-slate-700">
                        <a href="{{ route('register') }}" class="font-semibold text-sky-800 hover:underline">Create a free account</a>
                        or <a href="{{ route('login') }}" class="font-semibold text-sky-800 hover:underline">log in</a>
                        to duplicate this rig.
                    </p>
                @endauth
            @else
            <h2 class="text-lg font-bold text-slate-900 mb-3">Save this rig</h2>

            @auth
                <form method="POST" action="{{ $canUpdate ? route('tools.rigs.update', $rig) : route('tools.rigs.store') }}" class="space-y-4">
                    @csrf
                    @if ($canUpdate)
                        @method('PUT')
                    @endif

                    <div>
                        <label for="rig-name" class="block text-sm font-semibold mb-1">Rig name</label>
                        <input id="rig-name" name="name" type="text" required maxlength="120" x-model="rigName"
                               placeholder="e.g. The Standard Deep-Water Rig" class="{{ $inputClass }}">
                        @error('name')
                            <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($venues !== [])
                        <fieldset>
                            <legend class="text-sm font-semibold mb-2">Venues and pegs</legend>
                            <p class="text-sm text-slate-600 mb-2">Tick the venues this rig is for. Pegs are optional.</p>
                            <div class="max-h-64 space-y-3 overflow-y-auto rounded-lg border-2 border-slate-200 p-3">
                                <template x-for="venue in venues" :key="venue.id">
                                    <div>
                                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                                            <input type="checkbox" name="venue_ids[]" :value="venue.id" x-model="venueIds"
                                                   class="rounded border-slate-400 text-sky-800">
                                            <span x-text="venue.name"></span>
                                        </label>
                                        <div class="mt-1 space-y-1 ps-6" x-show="venueChosen(venue.id)">
                                            <template x-for="peg in venue.pegs" :key="peg.id">
                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                    <input type="checkbox" name="peg_ids[]" :value="peg.id" x-model="pegIds"
                                                           class="rounded border-slate-400 text-sky-800">
                                                    <span x-text="peg.label"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            @error('venue_ids')
                                <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                            @enderror
                            @error('peg_ids')
                                <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                            @enderror
                            @error('peg_ids.*')
                                <p class="text-sm text-red-700 mt-1">{{ $message }}</p>
                            @enderror
                        </fieldset>
                    @else
                        <p class="text-sm text-slate-600">No venues have verified pegs yet. You can still save the rig and attach a venue later.</p>
                    @endif

                    <div>
                        <label for="rig-notes" class="block text-sm font-semibold mb-1">Notes (optional)</label>
                        <textarea id="rig-notes" name="notes" rows="2" maxlength="2000"
                                  placeholder="e.g. Fish came 6in off bottom" class="{{ $inputClass }}">{{ old('notes', $rig?->notes ?? '') }}</textarea>
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
                    <input type="hidden" name="placements" :value="placementsPayload">
                    @error('placements')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="px-5 py-3 rounded-md bg-sky-800 text-white font-bold hover:bg-sky-900">
                        Save rig
                    </button>
                </form>
            @else
                <p class="text-sm text-slate-700">
                    <a href="{{ route('register') }}" class="font-semibold text-sky-800 hover:underline">Create a free account</a>
                    or <a href="{{ route('login') }}" class="font-semibold text-sky-800 hover:underline">log in</a>
                    to save this rig.
                </p>
            @endauth
            @endif
        </section>

        <section class="bg-paper border-2 border-slate-300 rounded-xl p-5">
            <h2 class="text-lg font-bold text-slate-900 mb-2">Before you fish</h2>
            <ul class="space-y-1 text-sm text-slate-700">
                <template x-for="(tip, index) in generalTips" :key="'general-' + index">
                    <li x-text="'• ' + tip"></li>
                </template>
            </ul>
            <p class="mt-2 text-sm text-slate-700" x-show="parsed" x-cloak>
                <template x-if="parsed?.loadedGrams">
                    <span>
                        Float already carries <span x-text="formatGrams(parsed.loadedGrams)"></span>;
                        add <span x-text="formatGrams(parsed.grams)"></span> of shot.
                    </span>
                </template>
                <template x-if="! parsed?.loadedGrams">
                    <span>Float load used: <span x-text="formatGrams(parsed?.grams ?? 0)"></span>.</span>
                </template>
            </p>
            <p class="mt-2 text-sm text-slate-700">
                New to shotting patterns? Read the
                <a href="{{ route('tools.shot-guide') }}" class="font-semibold text-sky-800 hover:underline">shot guide</a>
                for shot weights, pole float sizes and what each pattern is for.
            </p>
        </section>
    </div>
</x-app-layout>
