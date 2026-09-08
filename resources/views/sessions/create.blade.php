@use('App\Models\SessionCatch')
@use('App\Support\Weight')

<x-app-layout>
    @php
        $editing = filled($session ?? null);
        $selectedVenueId = old('venue_id', $editing ? $session->venue_id : ($venue->id ?? ''));

        $unit = old('weight_unit', old('catches.0.entered_unit', $preferredWeightUnit ?? Weight::UNIT_LB_OZ));
        $unit = Weight::normaliseUnit($unit);

        // Existing catches come back as grams; split them into whichever unit
        // boxes the form is showing so the values survive a round trip.
        $catchToFormRow = function (SessionCatch $c) use ($unit) {
            $weight = $c->weight();

            return [
                'species_id' => (string) $c->species_id,
                'entry_type' => $c->entry_type,
                'entered_unit' => $c->entered_unit ?: $unit,
                'weight_lb' => $weight?->pounds() ?: '',
                'weight_oz' => $weight?->ounces() ?: '',
                'weight_kg' => $weight ? $weight->kilograms() : '',
                'bait' => $c->bait,
                'quantity' => $c->quantity,
                'is_notable' => (bool) $c->is_notable,
            ];
        };

        $defaultCatches = old('catches', $editing && $session->catches->isNotEmpty()
            ? $session->catches->map($catchToFormRow)->values()->all()
            : []);

        // Open in bag mode if that is what this session already uses.
        $defaultCatchMode = old(
            'catch_mode',
            collect($defaultCatches)->contains(fn ($c) => ($c['entry_type'] ?? '') === SessionCatch::TYPE_BAG)
                ? SessionCatch::TYPE_BAG
                : SessionCatch::TYPE_INDIVIDUAL,
        );

        $tacticsTip = old('tactics_tip', $editing ? ($session->venueTactic?->body ?? $session->tactics_tip) : '');
        $initialPegX = old('peg_map_x', $editing ? $session->pegMapX() : null);
        $initialPegY = old('peg_map_y', $editing ? $session->pegMapY() : null);
        $initialWaterId = old(
            'water_id',
            $editing ? ($session->water_id ?? $session->waterPeg?->water_id ?? null) : null,
        );
        if ($initialWaterId === null || $initialWaterId === '') {
            $initialWaterId = 'all';
        } else {
            $initialWaterId = (string) $initialWaterId;
        }

        // Land the angler on the step holding the first validation error.
        $stepForField = function (string $field): int {
            if (str_starts_with($field, 'catches')) {
                return 2;
            }

            if (in_array($field, ['commentary', 'tactics_tip'], true) || str_starts_with($field, 'photos') || str_starts_with($field, 'remove_photo_ids')) {
                return 3;
            }

            return 1;
        };
        $initialStep = 1;
        foreach ($errors->keys() as $errorKey) {
            $initialStep = $stepForField($errorKey);
            break;
        }

        $speciesForVenue = $speciesByVenue ?? [];
    @endphp

    <x-slot name="header">
        @php
            $sessionBreadcrumbs = [
                ['label' => 'Venues', 'url' => route('venues.index')],
            ];
            $breadcrumbVenue = $editing ? $session->venue : ($venue ?? null);
            if ($breadcrumbVenue) {
                $sessionBreadcrumbs[] = [
                    'label' => $breadcrumbVenue->name,
                    'url' => route('venues.show', $breadcrumbVenue),
                ];
            }
            $sessionBreadcrumbs[] = [
                'label' => $editing ? 'Edit session' : 'Log session',
            ];
        @endphp
        <x-breadcrumbs :items="$sessionBreadcrumbs" />
        <h1 class="text-2xl font-bold text-slate-900">{{ $editing ? 'Edit fishing session' : 'Log a fishing session' }}</h1>
        <p class="text-slate-600 mt-1">Three quick steps: where you fished, what you caught, then how it went.</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="sessionForm({
            step: @js($initialStep),
            venueId: @js((string) $selectedVenueId),
            watersByVenue: @js($watersJson),
            venuesById: @js($venuesJson),
            pegsByVenue: @js($pegsJson),
            speciesByVenue: @js($speciesForVenue),
            allSpecies: @js($species->map(fn ($s) => ['id' => (string) $s->id, 'name' => $s->name])->values()->all()),
            catches: @js($defaultCatches),
            catchMode: @js($defaultCatchMode),
            weightUnit: @js($unit),
            pegMode: @js(old('peg_mode', $editing && $session->water_peg_id ? 'existing' : ($editing && $session->hasPegLocation() ? 'new' : 'existing'))),
            waterPegId: @js((string) old('water_peg_id', $editing ? ($session->water_peg_id ?? '') : '')),
            waterId: @js((string) $initialWaterId),
            pegX: @js($initialPegX !== null && $initialPegX !== '' ? (float) $initialPegX : null),
            pegY: @js($initialPegY !== null && $initialPegY !== '' ? (float) $initialPegY : null),
         })">
        <form method="POST"
              action="{{ $editing ? route('sessions.update', $session) : route('sessions.store') }}"
              enctype="multipart/form-data"
              @submit="onSubmit($event)"
              class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-5">
            @csrf
            @if ($editing)
                @method('PATCH')
            @endif

            <input type="hidden" name="catch_mode" :value="catchMode">
            <input type="hidden" name="weight_unit" :value="weightUnit">

            {{-- Step indicator --}}
            <ol class="flex items-center gap-2 text-sm font-semibold" aria-label="Progress">
                @foreach (['Where & when', 'What you caught', 'Story & photos'] as $i => $stepLabel)
                    @php $stepNumber = $i + 1; @endphp
                    <li class="flex-1">
                        <button type="button"
                                @click="goToStep({{ $stepNumber }})"
                                class="w-full min-h-11 rounded-lg border-2 px-2 py-2 text-left transition"
                                :class="step === {{ $stepNumber }}
                                    ? 'border-sky-700 bg-sky-50 text-sky-900'
                                    : (step > {{ $stepNumber }} ? 'border-slate-300 bg-white text-slate-700' : 'border-slate-200 bg-slate-50 text-slate-500')"
                                :aria-current="step === {{ $stepNumber }} ? 'step' : false">
                            <span class="block text-xs opacity-70">Step {{ $stepNumber }}</span>
                            <span class="block leading-tight">{{ $stepLabel }}</span>
                        </button>
                    </li>
                @endforeach
            </ol>

            @if ($errors->any())
                <div class="rounded-md border-2 border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="rounded-md border-2 border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900"
               x-show="stepError"
               x-cloak
               x-text="stepError"></p>

            {{-- ============================ STEP 1 ============================ --}}
            <div x-show="step === 1" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Venue</label>
                    <select name="venue_id" x-model="venueId" @change="onVenueChange()" :required="step === 1" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                        <option value="">Select venue</option>
                        @foreach ($venues as $item)
                            <option value="{{ $item->id }}" @selected($selectedVenueId == $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('venue_id') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Water / lake</label>
                    <select name="water_id" x-model="waterId" @change="onWaterChange()" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                        <option value="all">Whole venue / not sure</option>
                        <template x-for="water in currentWaters" :key="'water-' + water.id">
                            <option :value="String(water.id)" :selected="String(waterId) === String(water.id)" x-text="water.name"></option>
                        </template>
                    </select>
                    @error('water_id') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Date fished</label>
                        <input type="date" name="fished_at" x-model="fishedAt" :required="step === 1" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Duration (hours)</label>
                        <input type="number" name="duration_hours" value="{{ old('duration_hours', $editing ? $session->duration_hours : '') }}" min="1" max="72" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold mb-1">Weather</label>
                        <input name="weather" value="{{ old('weather', $editing ? $session->weather : '') }}" placeholder="Overcast, light SW wind" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                    </div>
                </div>

                {{-- Peg details are optional, so they stay folded away until wanted. --}}
                <div x-data="{ open: @js($initialStep === 1 && ($errors->hasAny(['water_peg_id', 'peg_map_x', 'peg_map_y', 'peg_photos', 'peg_number', 'peg_name']) || ($editing && ($session->water_peg_id || $session->hasPegLocation())))) }"
                     class="border-2 border-slate-200 rounded-xl">
                    <button type="button"
                            @click="open = !open"
                            class="w-full min-h-11 flex items-center justify-between px-4 py-3 text-left">
                        <span>
                            <span class="block text-sm font-semibold">Peg details</span>
                            <span class="block text-xs text-slate-500">Optional — pick a peg or mark a new one on the pond map</span>
                        </span>
                        <span class="text-slate-500 text-sm font-semibold" x-text="open ? 'Hide' : 'Add'"></span>
                    </button>

                    <div x-show="open" x-cloak class="space-y-3 px-4 pb-4">
                        <div>
                            <div class="flex flex-wrap gap-3 text-sm font-semibold">
                                <label class="inline-flex items-center gap-2 min-h-11">
                                    <input type="radio" name="peg_mode" value="existing" x-model="pegMode"> Existing peg
                                </label>
                                <label class="inline-flex items-center gap-2 min-h-11">
                                    <input type="radio" name="peg_mode" value="new" x-model="pegMode"> Add new peg
                                </label>
                                <label class="inline-flex items-center gap-2 min-h-11">
                                    <input type="radio" name="peg_mode" value="none" x-model="pegMode"> No peg
                                </label>
                            </div>
                        </div>

                        <div x-show="pegMode === 'existing'" x-cloak>
                            <select name="water_peg_id" x-model="waterPegId" :disabled="pegMode !== 'existing'" @change="selectExistingPeg()" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                                <option value="">Select peg</option>
                                <template x-for="peg in currentPegs" :key="'peg-' + peg.id">
                                    <option :value="String(peg.id)" :selected="String(waterPegId) === String(peg.id)" x-text="peg.label + (peg.verified ? '' : ' (your pending peg)') + (peg.x == null || peg.y == null ? ' (no map pin)' : '')"></option>
                                </template>
                            </select>
                            @error('water_peg_id') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-slate-500 mt-2" x-show="!venueId">Select a venue first to see its pegs.</p>
                            <p class="text-xs text-slate-500 mt-2" x-show="venueId && isWholeVenue && currentPegs.length > 0">Showing pegs from every water at this venue. Pick a specific water to use the pond map, or choose a peg from the list.</p>
                            <p class="text-xs text-slate-500 mt-2" x-show="venueId && currentPegs.length === 0">No pegs listed yet — add a new one (choose a specific water first).</p>
                            <p class="text-xs text-slate-500 mt-2" x-show="venueId && !isWholeVenue && selectedWaterMapUrl && mappedPegs.length > 0">You can also click a peg pin on the pond map below.</p>
                        </div>

                        <div x-show="pegMode === 'new'" x-cloak class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-semibold mb-1">Peg number</label>
                                <input name="peg_number" :disabled="pegMode !== 'new'" value="{{ old('peg_number', $editing && ! $session->water_peg_id ? $session->peg_number : '') }}" placeholder="e.g. 12" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-1">Peg name</label>
                                <input name="peg_name" :disabled="pegMode !== 'new'" value="{{ old('peg_name') }}" placeholder="e.g. Island, Car park end" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                            </div>
                        </div>

                        <div x-show="pegMode === 'new' && isWholeVenue" x-cloak class="mt-1">
                            <p class="text-sm text-amber-800 font-semibold">Choose a specific water/lake above before adding a new peg.</p>
                        </div>

                        <div x-show="pegMode === 'new' && !isWholeVenue" x-cloak class="mt-3">
                            <label class="block text-sm font-semibold mb-1">Peg photos</label>
                            <p class="text-sm text-slate-600 mb-2">Optional. Photos of a new peg stay pending until the venue owner verifies the peg.</p>
                            <input type="file" name="peg_photos[]" accept="image/*" multiple :disabled="pegMode !== 'new'" class="block w-full text-sm">
                            @error('peg_photos') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                            @error('peg_photos.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div x-show="showPegMap" x-cloak>
                            <div class="flex flex-wrap items-end justify-between gap-3 mb-2">
                                <div>
                                    <label class="block text-sm font-semibold mb-1" x-text="pegMode === 'new' ? 'Mark peg on pond map' : 'Select peg on pond map'"></label>
                                    <p class="text-sm text-slate-600" x-show="pegMode === 'new'">Zoom in for precision, then click the top-down pond image to place the peg.</p>
                                    <p class="text-sm text-slate-600" x-show="pegMode === 'existing'">Click a pin to select that peg, or use the dropdown above.</p>
                                </div>
                                <button type="button"
                                        @click="clearPegLocation()"
                                        x-show="pegMode === 'new' && pegX !== null && pegY !== null"
                                        class="text-sm font-semibold text-slate-700 hover:text-sky-800 min-h-11">
                                    Clear pin
                                </button>
                            </div>

                            <template x-if="! selectedWaterMapUrl">
                                <p class="text-sm text-amber-900 bg-amber-50 border-2 border-amber-400 rounded-lg p-3">
                                    This water does not have a pond map image yet. Ask a venue manager to upload one on the venue page before adding pegs.
                                </p>
                            </template>
                            <div x-show="selectedWaterMapUrl" x-cloak class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" @click="zoomIn()" class="px-3 py-1.5 min-h-11 rounded-md border-2 border-slate-400 bg-white text-sm font-semibold hover:bg-slate-50">Zoom in</button>
                                    <button type="button" @click="zoomOut()" class="px-3 py-1.5 min-h-11 rounded-md border-2 border-slate-400 bg-white text-sm font-semibold hover:bg-slate-50">Zoom out</button>
                                    <button type="button" @click="resetView()" class="px-3 py-1.5 min-h-11 rounded-md border-2 border-slate-400 bg-white text-sm font-semibold hover:bg-slate-50">Reset</button>
                                    <p class="text-xs text-slate-500" x-show="pegMode === 'new'">Scroll to zoom · drag to pan when zoomed · click to place</p>
                                    <p class="text-xs text-slate-500" x-show="pegMode === 'existing'">Scroll to zoom · drag to pan when zoomed · click a pin to select</p>
                                </div>
                                <div
                                    class="relative overflow-hidden rounded-lg border-2 border-slate-400 bg-slate-100 touch-none select-none"
                                    style="min-height: 12rem;"
                                    @wheel.prevent="onWheel($event)"
                                    @pointerdown="onPointerDown($event)"
                                    @pointermove="onPointerMove($event)"
                                    @pointerup="onPointerUp($event)"
                                    @pointercancel="onPointerUp($event)"
                                >
                                    <div
                                        class="flex justify-center origin-center will-change-transform"
                                        :style="`transform: translate(${panX}px, ${panY}px) scale(${scale});`"
                                        :class="pegMode === 'new' && scale <= 1 ? 'cursor-crosshair' : (scale > 1 ? 'cursor-grab' : 'cursor-default')"
                                    >
                                        <div x-ref="mapLayer" class="relative inline-block max-w-full">
                                            <img :src="selectedWaterMapUrl"
                                                 alt="Pond map"
                                                 draggable="false"
                                                 class="pointer-events-none block max-h-72 max-w-full h-auto w-auto">

                                            <template x-for="peg in existingMapPins" :key="'pin-' + peg.id">
                                                <button
                                                    type="button"
                                                    class="absolute z-10 h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-sky-700 shadow-md ring-2 ring-sky-900/40 hover:scale-125 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-sky-700"
                                                    :class="String(waterPegId) === String(peg.id) ? 'scale-125 ring-amber-400 bg-amber-500' : ''"
                                                    :style="`left:${peg.x}%; top:${peg.y}%;`"
                                                    :title="peg.label"
                                                    :aria-label="'Select peg ' + peg.label"
                                                    :aria-pressed="String(waterPegId) === String(peg.id) ? 'true' : 'false'"
                                                    @click.stop="selectPegFromMap(peg)"
                                                    @pointerdown.stop
                                                ></button>
                                            </template>

                                            <span
                                                x-show="pegMode === 'new' && pegX !== null && pegY !== null"
                                                x-cloak
                                                class="pointer-events-none absolute z-10 h-5 w-5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-sky-700 shadow-md ring-2 ring-sky-900/40"
                                                :style="pegX !== null && pegY !== null ? `left:${pegX}%; top:${pegY}%;` : ''"
                                            ></span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="peg_map_x" :disabled="pegMode !== 'new'" :value="pegMode === 'new' ? (pegX ?? '') : ''">
                                <input type="hidden" name="peg_map_y" :disabled="pegMode !== 'new'" :value="pegMode === 'new' ? (pegY ?? '') : ''">
                                <p class="text-xs text-slate-500 mt-2" x-show="pegMode === 'existing' && mappedPegs.length === 0" x-cloak>
                                    No pegs are placed on this pond map yet — pick from the dropdown, or add a new peg.
                                </p>
                                <p class="text-xs text-slate-500 mt-2" x-show="pegMode === 'existing' && selectedPegLabel" x-cloak>
                                    Selected: <span class="font-semibold text-slate-700" x-text="selectedPegLabel"></span>
                                </p>
                                <p class="text-xs text-slate-500 mt-2" x-show="pegMode === 'new' && pegX !== null && pegY !== null" x-cloak>
                                    Position <span x-text="Number(pegX).toFixed(1)"></span>%, <span x-text="Number(pegY).toFixed(1)"></span>%
                                </p>
                            </div>
                            @error('peg_map_x') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                            @error('peg_map_y') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================ STEP 2 ============================ --}}
            <div x-show="step === 2" class="space-y-5">
                {{-- How the angler wants to log --}}
                <div>
                    <span class="block text-sm font-semibold mb-2">How do you want to log your fish?</span>
                    <div class="grid sm:grid-cols-2 gap-2" role="group" aria-label="Catch logging mode">
                        <button type="button"
                                @click="setCatchMode('individual')"
                                :aria-pressed="catchMode === 'individual' ? 'true' : 'false'"
                                class="text-left rounded-lg border-2 px-4 py-3 min-h-11 transition"
                                :class="catchMode === 'individual' ? 'border-sky-700 bg-sky-50' : 'border-slate-300 bg-white hover:border-slate-400'">
                            <span class="block font-semibold text-sm">Every fish</span>
                            <span class="block text-xs text-slate-600 mt-0.5">One entry per fish, each with its own weight. Best for carp and specimen sessions.</span>
                        </button>
                        <button type="button"
                                @click="setCatchMode('bag')"
                                :aria-pressed="catchMode === 'bag' ? 'true' : 'false'"
                                class="text-left rounded-lg border-2 px-4 py-3 min-h-11 transition"
                                :class="catchMode === 'bag' ? 'border-sky-700 bg-sky-50' : 'border-slate-300 bg-white hover:border-slate-400'">
                            <span class="block font-semibold text-sm">Bag total</span>
                            <span class="block text-xs text-slate-600 mt-0.5">A count and total weight per species, then single out your best fish.</span>
                        </button>
                    </div>
                </div>

                {{-- Units apply to everything on this step --}}
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-semibold">Weigh in</span>
                    <div class="inline-flex rounded-lg border-2 border-slate-300 overflow-hidden">
                        <button type="button"
                                @click="setWeightUnit('lb_oz')"
                                :aria-pressed="weightUnit === 'lb_oz' ? 'true' : 'false'"
                                class="px-4 py-2 min-h-11 text-sm font-semibold transition"
                                :class="weightUnit === 'lb_oz' ? 'bg-sky-700 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'">
                            lb &amp; oz
                        </button>
                        <button type="button"
                                @click="setWeightUnit('kg')"
                                :aria-pressed="weightUnit === 'kg' ? 'true' : 'false'"
                                class="px-4 py-2 min-h-11 text-sm font-semibold transition border-l-2 border-slate-300"
                                :class="weightUnit === 'kg' ? 'bg-sky-700 text-white' : 'bg-white text-slate-700 hover:bg-slate-50'">
                            kg
                        </button>
                    </div>
                    <span class="text-xs text-slate-500">We'll remember this for next time.</span>
                </div>

                @error('catches') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror

                {{-- ---------------- Bag totals (bag mode only) ---------------- --}}
                <div x-show="catchMode === 'bag'" x-cloak class="space-y-3">
                    <h2 class="text-sm font-semibold">Your bag</h2>

                    <template x-for="(c, index) in catches" :key="'bag-' + c._id">
                        <div x-show="c.entry_type === 'bag'"
                             x-cloak
                             class="border-2 border-slate-200 rounded-xl p-4 space-y-3">
                            {{-- Disabled fieldsets are omitted from the POST, so hidden cards cannot clobber visible ones. --}}
                            <fieldset :disabled="c.entry_type !== 'bag'" class="min-w-0 space-y-3 border-0 p-0">
                                <input type="hidden" :name="`catches[${index}][entry_type]`" value="bag">
                                <input type="hidden" :name="`catches[${index}][is_notable]`" value="0">

                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <label class="block text-sm font-semibold mb-1">Species</label>
                                        <select :name="`catches[${index}][species_id]`" x-model="c.species_id" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                                            <option value="">Choose species</option>
                                            @foreach ($species as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" @click="removeCatch(index)" class="mt-6 min-h-11 px-3 text-sm font-semibold text-red-700 hover:text-red-900" :aria-label="'Remove ' + (speciesName(c.species_id) || 'entry')">Remove</button>
                                </div>

                                <div x-show="likelySpecies.length" x-cloak class="flex flex-wrap gap-2">
                                    <template x-for="s in likelySpecies" :key="'bag-chip-' + index + '-' + s.id">
                                        <button type="button"
                                                @click="c.species_id = s.id"
                                                class="min-h-11 rounded-full border-2 px-3 py-1.5 text-xs font-semibold transition"
                                                :class="String(c.species_id) === String(s.id) ? 'border-sky-700 bg-sky-700 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-sky-700'"
                                                x-text="s.name"></button>
                                    </template>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-semibold mb-1">Number of fish</label>
                                        <input type="number" inputmode="numeric" min="1" max="2000" :name="`catches[${index}][quantity]`" x-model="c.quantity" class="w-28 min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                                    </div>
                                    @include('sessions.partials.weight-fields', ['label' => 'Total weight'])
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold mb-1">Bait</label>
                                    <input :name="`catches[${index}][bait]`" x-model="c.bait" placeholder="Maggot, pellet…" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                                </div>
                            </fieldset>
                        </div>
                    </template>

                    <button type="button" @click="addBag()" class="w-full min-h-11 rounded-lg border-2 border-dashed border-slate-400 px-4 py-3 text-sm font-semibold text-sky-800 hover:border-sky-700 hover:bg-sky-50">
                        + Add a species to the bag
                    </button>
                </div>

                {{-- ---------------- Individual fish ---------------- --}}
                <div class="space-y-3">
                    <h2 class="text-sm font-semibold" x-text="catchMode === 'bag' ? 'Standout fish' : 'Fish caught'"></h2>
                    <p class="text-xs text-slate-600" x-show="catchMode === 'bag'" x-cloak>
                        Optional — single out the better fish from your bag so they show on the venue page.
                    </p>

                    <template x-for="(c, index) in catches" :key="'fish-' + c._id">
                        <div x-show="c.entry_type === 'individual'"
                             x-cloak
                             class="border-2 border-slate-200 rounded-xl p-4 space-y-3">
                            <fieldset :disabled="c.entry_type !== 'individual'" class="min-w-0 space-y-3 border-0 p-0">
                                <input type="hidden" :name="`catches[${index}][entry_type]`" value="individual">
                                <input type="hidden" :name="`catches[${index}][quantity]`" value="1">
                                <input type="hidden" :name="`catches[${index}][is_notable]`" :value="catchMode === 'bag' ? '1' : '0'">

                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <label class="block text-sm font-semibold mb-1">Species</label>
                                        <select :name="`catches[${index}][species_id]`" x-model="c.species_id" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                                            <option value="">Choose species</option>
                                            @foreach ($species as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" @click="removeCatch(index)" class="mt-6 min-h-11 px-3 text-sm font-semibold text-red-700 hover:text-red-900" :aria-label="'Remove ' + (speciesName(c.species_id) || 'fish')">Remove</button>
                                </div>

                                {{-- Quick-pick the species stocked in the chosen water --}}
                                <div x-show="likelySpecies.length" x-cloak class="flex flex-wrap gap-2">
                                    <template x-for="s in likelySpecies" :key="'chip-' + index + '-' + s.id">
                                        <button type="button"
                                                @click="c.species_id = s.id"
                                                class="min-h-11 rounded-full border-2 px-3 py-1.5 text-xs font-semibold transition"
                                                :class="String(c.species_id) === String(s.id) ? 'border-sky-700 bg-sky-700 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-sky-700'"
                                                x-text="s.name"></button>
                                    </template>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-3">
                                    @include('sessions.partials.weight-fields', ['label' => 'Weight'])
                                    <div>
                                        <label class="block text-sm font-semibold mb-1">Bait</label>
                                        <input :name="`catches[${index}][bait]`" x-model="c.bait" placeholder="Boilie, worm…" class="w-full min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </template>

                    <div class="grid sm:grid-cols-2 gap-2">
                        <button type="button" @click="addIndividual()" class="w-full min-h-11 rounded-lg border-2 border-dashed border-slate-400 px-4 py-3 text-sm font-semibold text-sky-800 hover:border-sky-700 hover:bg-sky-50">
                            <span x-text="catchMode === 'bag' ? '+ Single out a good fish' : '+ Add a fish'"></span>
                        </button>
                        <button type="button"
                                @click="addSimilarIndividual()"
                                x-show="lastIndividual"
                                x-cloak
                                class="w-full min-h-11 rounded-lg border-2 border-dashed border-slate-400 px-4 py-3 text-sm font-semibold text-sky-800 hover:border-sky-700 hover:bg-sky-50">
                            + Another <span x-text="speciesName(lastIndividual?.species_id) || 'of the same'"></span>
                        </button>
                    </div>
                </div>

                <p class="text-sm text-slate-600" x-show="catches.length === 0" x-cloak>
                    Blanked? No problem — leave this empty and carry on.
                </p>

                {{-- Running tally --}}
                <div x-show="tally.count > 0" x-cloak class="rounded-lg border-2 border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <span class="font-semibold">Session total:</span>
                    <span x-text="tally.count"></span> <span x-text="tally.count === 1 ? 'fish' : 'fish'"></span>
                    <template x-if="tally.label"><span> · <span class="font-semibold" x-text="tally.label"></span></span></template>
                </div>
            </div>

            {{-- ============================ STEP 3 ============================ --}}
            <div x-show="step === 3" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Commentary / write-up</label>
                    <textarea name="commentary" rows="5" class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ old('commentary', $editing ? $session->commentary : '') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Tactics tip for the venue guide</label>
                    <p class="text-sm text-slate-600 mb-2">Share what worked — baits, pegs, conditions. This appears in the venue’s tactics section for other anglers.</p>
                    <textarea name="tactics_tip" rows="4" placeholder="e.g. Margin pole with maggot and caster on peg 12 in a warm south-westerly." class="w-full rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">{{ $tacticsTip }}</textarea>
                    @error('tactics_tip') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Photos (up to 6, mobile friendly)</label>
                    @if ($editing && $session->photos->isNotEmpty())
                        <div class="mb-3"
                             x-data="{
                                removed: @js(collect(old('remove_photo_ids', []))->map(fn ($id) => (int) $id)->values()->all()),
                                toggle(id) {
                                    if (this.removed.includes(id)) {
                                        this.removed = this.removed.filter((item) => item !== id);
                                    } else {
                                        this.removed.push(id);
                                    }
                                },
                             }">
                            <p class="text-sm text-slate-600 mb-2">Mark any to remove, then save. You can also add more below.</p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @php
                                    $sessionPhotoGallery = $session->photos->map(fn ($item) => [
                                        'url' => $item->url(),
                                        'alt' => 'Session photo',
                                    ])->values()->all();
                                @endphp
                                @foreach ($session->photos as $photo)
                                    <figure class="relative rounded-lg border-2 overflow-hidden bg-slate-100"
                                            :class="removed.includes({{ $photo->id }}) ? 'border-red-400 opacity-50' : 'border-slate-300'">
                                        <button
                                            type="button"
                                            class="block w-full text-left"
                                            @click="$store.photoLightbox.open(@js($sessionPhotoGallery), {{ $loop->index }}, 'Session photo')"
                                        >
                                            <img src="{{ $photo->url() }}" alt="Session photo" class="object-cover h-28 w-full hover:opacity-95">
                                        </button>
                                        <button type="button"
                                                @click="toggle({{ $photo->id }})"
                                                class="absolute inset-x-0 bottom-0 text-white text-sm font-semibold py-1.5 min-h-11"
                                                :class="removed.includes({{ $photo->id }}) ? 'bg-sky-800 hover:bg-sky-900' : 'bg-slate-900/75 hover:bg-red-800'"
                                                x-text="removed.includes({{ $photo->id }}) ? 'Keep' : 'Remove'">
                                        </button>
                                    </figure>
                                @endforeach
                            </div>
                            <template x-for="id in removed" :key="'remove-photo-' + id">
                                <input type="hidden" name="remove_photo_ids[]" :value="id">
                            </template>
                        </div>
                    @endif
                    <input type="file" name="photos[]" accept="image/*" multiple class="block w-full text-sm">
                    @error('photos.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                    @error('remove_photo_ids') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                    @error('remove_photo_ids.*') <p class="text-red-700 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ============================ NAVIGATION ============================ --}}
            <div class="flex flex-wrap items-center gap-3 border-t-2 border-slate-200 pt-4">
                <button type="button"
                        @click="previousStep()"
                        x-show="step > 1"
                        x-cloak
                        class="px-5 py-3 min-h-11 rounded-md border-2 border-slate-400 bg-white font-semibold hover:bg-slate-50">
                    Back
                </button>

                <button type="button"
                        @click="nextStep()"
                        x-show="step < 3"
                        class="px-5 py-3 min-h-11 rounded-md bg-sky-700 text-white font-bold hover:bg-sky-800">
                    Next
                </button>

                {{-- The write-up is optional, so saving is offered from the catch step on. --}}
                <button type="submit"
                        x-show="step >= 2"
                        x-cloak
                        class="px-5 py-3 min-h-11 rounded-md font-bold"
                        :class="step === 3 ? 'bg-sky-700 text-white hover:bg-sky-800' : 'border-2 border-sky-700 text-sky-800 bg-white hover:bg-sky-50'">
                    {{ $editing ? 'Save changes' : 'Save session' }}
                </button>
            </div>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            function sessionForm({ step, venueId, watersByVenue, venuesById, pegsByVenue, speciesByVenue, allSpecies, catches, catchMode, weightUnit, pegMode, waterPegId, waterId, pegX, pegY }) {
                const GRAMS_PER_POUND = 453.59237;
                const GRAMS_PER_OUNCE = 28.349523125;
                let catchUid = 0;

                const withUid = (row) => ({
                    species_id: '',
                    entry_type: 'individual',
                    weight_lb: '',
                    weight_oz: '',
                    weight_kg: '',
                    bait: '',
                    quantity: 1,
                    is_notable: false,
                    ...row,
                    _id: ++catchUid,
                });

                return {
                    step: step || 1,
                    stepError: '',
                    venueId: venueId || '',
                    waterId: waterId || 'all',
                    fishedAt: @js(old('fished_at', $editing ? $session->fished_at->toDateString() : now()->toDateString())),
                    watersByVenue: watersByVenue || {},
                    venuesById: venuesById || {},
                    pegsByVenue: pegsByVenue || {},
                    speciesByVenue: speciesByVenue || {},
                    allSpecies: allSpecies || [],
                    catches: (catches || []).map(withUid),
                    catchMode: catchMode || 'individual',
                    weightUnit: weightUnit || 'lb_oz',
                    pegMode: pegMode || 'existing',
                    waterPegId: waterPegId || '',
                    pegX,
                    pegY,
                    scale: 1,
                    panX: 0,
                    panY: 0,
                    minScale: 1,
                    maxScale: 5,
                    dragging: false,
                    dragMoved: false,
                    pointerId: null,
                    lastX: 0,
                    lastY: 0,

                    /* ---------------- steps ---------------- */
                    goToStep(target) {
                        // Moving forward has to clear the same checks as Next.
                        if (target > this.step && ! this.validateThrough(target)) {
                            return;
                        }
                        this.stepError = '';
                        this.step = target;
                        this.scrollToTop();
                    },
                    nextStep() {
                        if (! this.validateThrough(this.step + 1)) {
                            return;
                        }
                        this.stepError = '';
                        this.step = Math.min(3, this.step + 1);
                        this.scrollToTop();
                    },
                    previousStep() {
                        this.stepError = '';
                        this.step = Math.max(1, this.step - 1);
                        this.scrollToTop();
                    },
                    validateThrough(target) {
                        // Step 1 is the only one with fields the server insists on.
                        if (target > 1) {
                            if (! this.venueId) {
                                this.stepError = 'Choose a venue before moving on.';
                                this.step = 1;
                                return false;
                            }
                            if (! this.fishedAt) {
                                this.stepError = 'Add the date you fished before moving on.';
                                this.step = 1;
                                return false;
                            }
                        }

                        return true;
                    },
                    onSubmit(event) {
                        if (! this.validateThrough(2)) {
                            event.preventDefault();
                            this.scrollToTop();
                        }
                    },
                    scrollToTop() {
                        this.$nextTick(() => this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' }));
                    },

                    /* ---------------- catches ---------------- */
                    setCatchMode(mode) {
                        this.catchMode = mode;
                        // Leaving bag mode would orphan bag rows the angler can no longer see.
                        if (mode === 'individual') {
                            this.catches = this.catches.filter((c) => c.entry_type !== 'bag');
                            if (! this.catches.some((c) => c.entry_type === 'individual')) {
                                this.addIndividual();
                            }
                            return;
                        }

                        if (! this.catches.some((c) => c.entry_type === 'bag')) {
                            this.addBag();
                        }
                    },
                    setWeightUnit(unit) {
                        if (unit === this.weightUnit) {
                            return;
                        }
                        // Carry entered values across so switching units is not destructive.
                        this.catches.forEach((c) => this.convertRow(c, unit));
                        this.weightUnit = unit;
                    },
                    convertRow(c, toUnit) {
                        const grams = this.rowGrams(c);
                        if (grams === null) {
                            return;
                        }

                        if (toUnit === 'kg') {
                            c.weight_kg = Math.round(grams) / 1000;
                            return;
                        }

                        const totalOunces = Math.round(grams / GRAMS_PER_OUNCE);
                        c.weight_lb = Math.floor(totalOunces / 16) || '';
                        c.weight_oz = totalOunces % 16;
                    },
                    rowGrams(c) {
                        if (this.weightUnit === 'kg') {
                            return c.weight_kg === '' || c.weight_kg === null ? null : Number(c.weight_kg) * 1000;
                        }

                        const hasLb = c.weight_lb !== '' && c.weight_lb !== null;
                        const hasOz = c.weight_oz !== '' && c.weight_oz !== null;
                        if (! hasLb && ! hasOz) {
                            return null;
                        }

                        return Number(hasLb ? c.weight_lb : 0) * GRAMS_PER_POUND
                            + Number(hasOz ? c.weight_oz : 0) * GRAMS_PER_OUNCE;
                    },
                    conversionHint(c) {
                        const grams = this.rowGrams(c);
                        if (grams === null || grams <= 0) {
                            return '';
                        }

                        if (this.weightUnit === 'kg') {
                            const totalOunces = Math.round(grams / GRAMS_PER_OUNCE);
                            const lb = Math.floor(totalOunces / 16);
                            const oz = totalOunces % 16;
                            return `≈ ${lb ? lb + 'lb ' : ''}${oz}oz`;
                        }

                        return `≈ ${(Math.round(grams) / 1000).toFixed(2)} kg`;
                    },
                    addIndividual() {
                        this.catches.push(withUid({
                            entry_type: 'individual',
                            is_notable: this.catchMode === 'bag',
                        }));
                    },
                    addBag() {
                        this.catches.push(withUid({ entry_type: 'bag', quantity: 1 }));
                    },
                    addSimilarIndividual() {
                        const src = this.lastIndividual;
                        this.catches.push(withUid({
                            entry_type: 'individual',
                            species_id: src?.species_id || '',
                            bait: src?.bait || '',
                            is_notable: this.catchMode === 'bag',
                        }));
                    },
                    removeCatch(index) {
                        this.catches.splice(index, 1);
                    },
                    get lastIndividual() {
                        const rows = this.catches.filter((c) => c.entry_type === 'individual' && c.species_id);
                        return rows.length ? rows[rows.length - 1] : null;
                    },
                    speciesName(id) {
                        return this.allSpecies.find((s) => String(s.id) === String(id))?.name || '';
                    },
                    get likelySpecies() {
                        const ids = this.speciesByVenue[this.venueId] || this.speciesByVenue[String(this.venueId)] || [];
                        if (! ids.length) {
                            return [];
                        }

                        return this.allSpecies.filter((s) => ids.map(String).includes(String(s.id))).slice(0, 8);
                    },
                    get tally() {
                        let count = 0;
                        let grams = 0;
                        let anyWeight = false;

                        this.catches.forEach((c) => {
                            if (! c.species_id) {
                                return;
                            }
                            // Standout fish are already inside the bag count.
                            if (! (this.catchMode === 'bag' && c.entry_type === 'individual')) {
                                count += c.entry_type === 'bag' ? Number(c.quantity || 1) : 1;
                            }
                            const rowGrams = this.rowGrams(c);
                            if (rowGrams !== null && ! (this.catchMode === 'bag' && c.entry_type === 'individual')) {
                                grams += rowGrams;
                                anyWeight = true;
                            }
                        });

                        let label = '';
                        if (anyWeight && grams > 0) {
                            if (this.weightUnit === 'kg') {
                                label = `${(Math.round(grams) / 1000).toFixed(2)} kg`;
                            } else {
                                const totalOunces = Math.round(grams / GRAMS_PER_OUNCE);
                                label = `${Math.floor(totalOunces / 16)}lb ${totalOunces % 16}oz`;
                            }
                        }

                        return { count, label };
                    },

                    /* ---------------- venue / water / peg ---------------- */
                    get isWholeVenue() {
                        return ! this.waterId || this.waterId === 'all';
                    },
                    get currentWaters() {
                        return this.watersByVenue[this.venueId] || this.watersByVenue[String(this.venueId)] || [];
                    },
                    get selectedWater() {
                        if (this.isWholeVenue) {
                            return null;
                        }

                        return this.currentWaters.find((water) => String(water.id) === String(this.waterId)) || null;
                    },
                    get selectedWaterMapUrl() {
                        return this.selectedWater?.map_url || null;
                    },
                    get showPegMap() {
                        if (this.pegMode === 'new') {
                            return ! this.isWholeVenue;
                        }

                        if (this.pegMode === 'existing') {
                            return ! this.isWholeVenue && Boolean(this.selectedWaterMapUrl);
                        }

                        return false;
                    },
                    get currentPegs() {
                        const byWater = this.pegsByVenue[this.venueId] || this.pegsByVenue[String(this.venueId)] || {};

                        if (this.isWholeVenue) {
                            return Object.entries(byWater).flatMap(([waterKey, pegs]) => {
                                const water = this.currentWaters.find(item => String(item.id) === String(waterKey));
                                const waterName = water?.name;

                                return (pegs || []).map((peg) => ({
                                    ...peg,
                                    label: waterName ? `${peg.label} · ${waterName}` : peg.label,
                                }));
                            });
                        }

                        return byWater[this.waterId] || byWater[String(this.waterId)] || [];
                    },
                    get mappedPegs() {
                        return this.currentPegs.filter((peg) => peg.x !== null && peg.x !== undefined && peg.y !== null && peg.y !== undefined);
                    },
                    get existingMapPins() {
                        return this.pegMode === 'existing' ? this.mappedPegs : [];
                    },
                    get selectedPegLabel() {
                        const peg = this.currentPegs.find((item) => String(item.id) === String(this.waterPegId));

                        return peg?.label || '';
                    },
                    init() {
                        if (this.catches.length === 0) {
                            this.catchMode === 'bag' ? this.addBag() : this.addIndividual();
                        }

                        const desiredWaterId = this.waterId ? String(this.waterId) : 'all';
                        const desiredPegId = this.waterPegId ? String(this.waterPegId) : '';
                        this._syncingSelects = true;

                        if (! desiredWaterId || desiredWaterId === 'all') {
                            const inferred = this.waterIdForPeg(desiredPegId);
                            this.waterId = inferred || 'all';
                        } else {
                            this.waterId = desiredWaterId;
                        }

                        this.$nextTick(() => {
                            const waterId = this.waterId;
                            this.waterId = 'all';
                            this.$nextTick(() => {
                                this.waterId = waterId;
                                this.$nextTick(() => {
                                    if (desiredPegId) {
                                        this.waterPegId = '';
                                        this.$nextTick(() => {
                                            this.waterPegId = desiredPegId;
                                            this.selectExistingPeg();
                                            this._syncingSelects = false;
                                        });
                                    } else {
                                        this._syncingSelects = false;
                                    }
                                });
                            });
                        });
                    },
                    waterIdForPeg(pegId) {
                        if (! pegId || ! this.venueId) {
                            return null;
                        }

                        const byWater = this.pegsByVenue[this.venueId] || this.pegsByVenue[String(this.venueId)] || {};

                        for (const [waterKey, pegs] of Object.entries(byWater)) {
                            if ((pegs || []).some((peg) => String(peg.id) === String(pegId))) {
                                return String(waterKey);
                            }
                        }

                        return null;
                    },
                    placePeg(event) {
                        this.placeAtClientPoint(event.clientX, event.clientY);
                    },
                    placeAtClientPoint(clientX, clientY) {
                        const layer = this.$refs.mapLayer;
                        if (! layer) {
                            return;
                        }

                        const rect = layer.getBoundingClientRect();
                        if (! rect.width || ! rect.height) {
                            return;
                        }

                        if (
                            clientX < rect.left
                            || clientX > rect.right
                            || clientY < rect.top
                            || clientY > rect.bottom
                        ) {
                            return;
                        }

                        this.pegX = Math.min(100, Math.max(0, ((clientX - rect.left) / rect.width) * 100));
                        this.pegY = Math.min(100, Math.max(0, ((clientY - rect.top) / rect.height) * 100));
                    },
                    zoomIn() {
                        this.setScale(this.scale + 0.35);
                    },
                    zoomOut() {
                        this.setScale(this.scale - 0.35);
                    },
                    resetView() {
                        this.scale = 1;
                        this.panX = 0;
                        this.panY = 0;
                    },
                    setScale(next) {
                        this.scale = Math.min(this.maxScale, Math.max(this.minScale, Number(Number(next).toFixed(2))));
                        if (this.scale === 1) {
                            this.panX = 0;
                            this.panY = 0;
                        }
                    },
                    onWheel(event) {
                        const delta = event.deltaY > 0 ? -0.2 : 0.2;
                        this.setScale(this.scale + delta);
                    },
                    onPointerDown(event) {
                        if (event.button !== undefined && event.button !== 0) {
                            return;
                        }
                        if (event.target.closest('button, a, input, select, textarea, label')) {
                            return;
                        }
                        this.dragging = true;
                        this.dragMoved = false;
                        this.pointerId = event.pointerId;
                        this.lastX = event.clientX;
                        this.lastY = event.clientY;
                        event.currentTarget.setPointerCapture?.(event.pointerId);
                    },
                    onPointerMove(event) {
                        if (! this.dragging || this.pointerId !== event.pointerId) {
                            return;
                        }
                        const dx = event.clientX - this.lastX;
                        const dy = event.clientY - this.lastY;
                        if (this.scale > 1 && (Math.abs(dx) > 3 || Math.abs(dy) > 3)) {
                            this.dragMoved = true;
                        }
                        if (this.scale > 1) {
                            this.panX += dx;
                            this.panY += dy;
                        }
                        this.lastX = event.clientX;
                        this.lastY = event.clientY;
                    },
                    onPointerUp(event) {
                        if (this.pointerId !== null && event.pointerId !== this.pointerId) {
                            return;
                        }

                        const shouldPlace = this.pegMode === 'new' && this.dragging && ! this.dragMoved;
                        this.dragging = false;
                        this.pointerId = null;

                        if (shouldPlace) {
                            this.placeAtClientPoint(event.clientX, event.clientY);
                        }
                    },
                    clearPegLocation() {
                        this.pegX = null;
                        this.pegY = null;
                    },
                    selectExistingPeg() {
                        const peg = this.currentPegs.find(p => String(p.id) === String(this.waterPegId));
                        if (!peg) return;

                        if (this.isWholeVenue && peg.water_id) {
                            this.waterId = String(peg.water_id);
                        }

                        this.pegX = peg.x === null || peg.x === undefined ? null : Number(peg.x);
                        this.pegY = peg.y === null || peg.y === undefined ? null : Number(peg.y);
                    },
                    selectPegFromMap(peg) {
                        if (! peg) {
                            return;
                        }

                        this.pegMode = 'existing';
                        this.waterPegId = String(peg.id);

                        if (peg.water_id) {
                            this.waterId = String(peg.water_id);
                        }

                        this.pegX = peg.x === null || peg.x === undefined ? null : Number(peg.x);
                        this.pegY = peg.y === null || peg.y === undefined ? null : Number(peg.y);
                    },
                    onVenueChange() {
                        if (this._syncingSelects) {
                            return;
                        }
                        this.waterId = 'all';
                        this.waterPegId = '';
                        this.pegX = null;
                        this.pegY = null;
                        this.resetView();
                    },
                    onWaterChange() {
                        if (this._syncingSelects) {
                            return;
                        }
                        this.waterPegId = '';
                        if (this.pegMode === 'existing') {
                            this.pegX = null;
                            this.pegY = null;
                        }
                        this.resetView();
                    },
                }
            }
        </script>
    </x-slot>
</x-app-layout>
