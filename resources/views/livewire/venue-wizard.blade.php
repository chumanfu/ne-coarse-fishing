@assets
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @vite(['resources/css/venue-wizard.css'])
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endassets

<div class="venue-wizard">
    <ol class="venue-wizard-steps">
        @foreach ($steps as $number => $label)
            <li>
                <button type="button"
                        wire:click="goToStep({{ $number }})"
                        @disabled($number > $step)
                        class="venue-wizard-step {{ $step === $number ? 'is-current' : ($number < $step ? 'is-done' : '') }}">
                    <span class="venue-wizard-step-label">Step {{ $number }}</span>
                    {{ $label }}
                </button>
            </li>
        @endforeach
    </ol>

    <div class="venue-wizard-panel">
        @if ($step === 1)
            <div wire:key="step-location"
                 x-data="venueWizardMap(@js($latitude), @js($longitude), @js($locationSet))"
                 x-on:venue-location-updated.window="moveMarker($event.detail.lat, $event.detail.lng)">
                <h2>Find the water on the map</h2>
                <p class="hint">Search by UK postcode, place name, or paste latitude,longitude. Then drop or drag the pin.</p>

                <div class="row" style="margin-top: 1rem;">
                    <input type="text"
                           wire:model="searchQuery"
                           wire:keydown.enter.prevent="searchLocation"
                           placeholder="e.g. DH7 7AR or 54.79, -1.62"
                           style="flex: 1;">
                    <button type="button" wire:click="searchLocation" class="venue-wizard-btn venue-wizard-btn-dark">
                        Search
                    </button>
                </div>
                @error('searchQuery') <p class="error">{{ $message }}</p> @enderror
                @if ($searchError)
                    <p class="warn">{{ $searchError }}</p>
                @endif

                @if (count($searchResults))
                    <ul class="venue-wizard-results">
                        @foreach ($searchResults as $index => $result)
                            <li>
                                <button type="button" wire:click="selectSearchResult({{ $index }})">
                                    {{ $result['display_name'] }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div id="venue-wizard-map" class="venue-wizard-map" wire:ignore></div>

                <div class="grid-2" style="margin-top: 1rem;">
                    <div>
                        <label>Latitude</label>
                        <input type="number" step="any" wire:model.blur="latitude">
                    </div>
                    <div>
                        <label>Longitude</label>
                        <input type="number" step="any" wire:model.blur="longitude">
                    </div>
                </div>
                @error('locationSet') <p class="error">{{ $message }}</p> @enderror
                @error('latitude') <p class="error">{{ $message }}</p> @enderror
                @error('longitude') <p class="error">{{ $message }}</p> @enderror

                @if ($locationSet)
                    <p class="ok">Location selected. You can refine the pin before continuing.</p>
                @endif
            </div>
        @endif

        @if ($step === 2)
            <div wire:key="step-name">
                <h2>Name the venue</h2>
                <p class="hint">The URL slug is generated automatically from the name.</p>
                <div style="margin-top: 1rem;">
                    <label>Venue name</label>
                    <input type="text" wire:model.live.debounce.300ms="name" placeholder="e.g. Aldin Grange Lakes">
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div style="margin-top: 1rem;">
                    <label>Slug</label>
                    <input type="text" value="{{ $slugPreview }}" readonly>
                </div>
            </div>
        @endif

        @if ($step === 3)
            <div wire:key="step-overview">
                <h2>Overview</h2>
                <p class="hint">A short description of the fishery for anglers browsing the directory.</p>
                <div style="margin-top: 1rem;">
                    <label>Description / overview</label>
                    <textarea wire:model="overview" rows="8" placeholder="What makes this water worth a visit?"></textarea>
                    @error('overview') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        @if ($step === 4)
            <div wire:key="step-address">
                <h2>Address &amp; directions</h2>
                <p class="hint">Address is pre-filled from the map pin — edit if needed.</p>
                <div style="margin-top: 1rem;">
                    <label>Address</label>
                    <input type="text" wire:model="address">
                    @error('address') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div style="margin-top: 1rem;">
                    <label>Directions / parking</label>
                    <textarea wire:model="directions" rows="5" placeholder="How to find the car park, access tracks, gate codes if appropriate…"></textarea>
                    @error('directions') <p class="error">{{ $message }}</p> @enderror
                </div>
                <button type="button" wire:click="reverseGeocode" class="venue-wizard-btn-link" style="margin-top: 0.75rem;">
                    Refresh address from map pin
                </button>
            </div>
        @endif

        @if ($step === 5)
            <div wire:key="step-details">
                <h2>Tickets, waters &amp; local knowledge</h2>
                <p class="hint">Everything else anglers need before they visit.</p>

                @if ($admin)
                    <div class="venue-wizard-admin" style="margin-top: 1rem;">
                        <strong>Admin options</strong>
                        <label>
                            <input type="checkbox" wire:model="is_approved">
                            Approved for public listing
                        </label>
                        <label>
                            <input type="checkbox" wire:model="manager_verified">
                            Manager verified badge
                        </label>
                    </div>
                @endif

                <div class="grid-2" style="margin-top: 1rem;">
                    <div>
                        <label>Ticket type</label>
                        <select wire:model="ticket_type">
                            <option value="day_ticket">Day Ticket</option>
                            <option value="club">Club</option>
                            <option value="syndicate">Syndicate</option>
                            <option value="mixed">Mixed</option>
                        </select>
                    </div>
                    <div style="display: flex; align-items: end;">
                        <label style="display: inline-flex; align-items: center;">
                            <input type="checkbox" wire:model="is_complex">
                            Complex with multiple waters
                        </label>
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <label>Day ticket info</label>
                    <textarea wire:model="day_ticket_info" rows="3"></textarea>
                </div>
                <div style="margin-top: 1rem;">
                    <label>Membership info</label>
                    <textarea wire:model="membership_info" rows="3"></textarea>
                </div>
                <div style="margin-top: 1rem;">
                    <label>Opening times</label>
                    <textarea wire:model="opening_times" rows="2"></textarea>
                </div>
                <div style="margin-top: 1rem;">
                    <label>Seasonal restrictions</label>
                    <textarea wire:model="season_info" rows="2"></textarea>
                </div>
                <div style="margin-top: 1rem;">
                    <label>Tactics &amp; local knowledge</label>
                    <textarea wire:model="tactics_guide" rows="4"></textarea>
                </div>

                <div style="margin-top: 1.25rem;">
                    <div class="row" style="justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <h3 style="margin: 0; font-weight: 700;">Waters / ponds</h3>
                        <button type="button" wire:click="addWater" class="venue-wizard-btn venue-wizard-btn-outline">Add water</button>
                    </div>

                    @foreach ($waters as $index => $water)
                        <div class="venue-wizard-water" wire:key="water-{{ $index }}">
                            <div class="row" style="justify-content: space-between; align-items: center;">
                                <strong>Water {{ $index + 1 }}</strong>
                                @if (count($waters) > 1)
                                    <button type="button" wire:click="removeWater({{ $index }})" class="venue-wizard-btn-danger">Remove</button>
                                @endif
                            </div>
                            <div style="margin-top: 0.75rem;">
                                <label>Name</label>
                                <input type="text" wire:model="waters.{{ $index }}.name">
                                @error('waters.'.$index.'.name') <p class="error">{{ $message }}</p> @enderror
                            </div>
                            <div style="margin-top: 0.75rem;">
                                <label>Description</label>
                                <textarea wire:model="waters.{{ $index }}.description" rows="2"></textarea>
                            </div>
                            <div class="grid-2" style="margin-top: 0.75rem;">
                                <div>
                                    <label>Peg count</label>
                                    <input type="number" wire:model="waters.{{ $index }}.peg_count">
                                </div>
                                <div>
                                    <label>Depth info</label>
                                    <input type="text" wire:model="waters.{{ $index }}.depth_info">
                                </div>
                            </div>
                            <div style="margin-top: 0.75rem;">
                                <label>Species</label>
                                <div class="check-grid">
                                    @foreach ($speciesOptions as $species)
                                        <label>
                                            <input type="checkbox"
                                                   wire:model="waters.{{ $index }}.species"
                                                   value="{{ $species->id }}">
                                            {{ $species->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @error('waters') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif
    </div>

    <div class="venue-wizard-actions">
        <button type="button"
                wire:click="previousStep"
                @disabled($step === 1)
                class="venue-wizard-btn venue-wizard-btn-secondary">
            Back
        </button>

        <div class="row">
            @if ($step < 5)
                <button type="button" wire:click="nextStep" class="venue-wizard-btn venue-wizard-btn-primary">
                    Continue
                </button>
            @else
                <button type="button" wire:click="save" class="venue-wizard-btn venue-wizard-btn-primary">
                    {{ $venue ? 'Save changes' : ($admin ? 'Create venue' : 'Submit for approval') }}
                </button>
            @endif
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('venueWizardMap', (lat, lng, locationSet) => ({
        map: null,
        marker: null,
        init() {
            this.$nextTick(() => this.waitForLeaflet(lat, lng, locationSet));
        },
        waitForLeaflet(lat, lng, locationSet, attempt = 0) {
            if (typeof L !== 'undefined') {
                this.initMap(lat, lng, locationSet);
                return;
            }

            if (attempt > 40) {
                console.error('Leaflet failed to load for venue wizard map.');
                return;
            }

            setTimeout(() => this.waitForLeaflet(lat, lng, locationSet, attempt + 1), 100);
        },
        initMap(lat, lng, locationSet) {
            const el = document.getElementById('venue-wizard-map');
            if (!el || this.map) return;

            this.map = L.map(el).setView([lat || 54.7767, lng || -1.5753], locationSet ? 14 : 9);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            this.marker = L.marker([lat || 54.7767, lng || -1.5753], { draggable: true }).addTo(this.map);
            this.marker.on('dragend', () => {
                const pos = this.marker.getLatLng();
                this.$wire.setPin(Number(pos.lat.toFixed(7)), Number(pos.lng.toFixed(7)));
            });
            this.map.on('click', (e) => {
                this.$wire.setPin(Number(e.latlng.lat.toFixed(7)), Number(e.latlng.lng.toFixed(7)));
            });

            setTimeout(() => this.map.invalidateSize(), 120);
            setTimeout(() => this.map.invalidateSize(), 400);
        },
        moveMarker(lat, lng) {
            if (!this.marker || !this.map) return;
            this.marker.setLatLng([lat, lng]);
            this.map.setView([lat, lng], Math.max(this.map.getZoom(), 14));
            setTimeout(() => this.map.invalidateSize(), 50);
        }
    }));
</script>
@endscript
