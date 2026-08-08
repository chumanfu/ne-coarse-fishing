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
                    <label>Website URL</label>
                    <input type="url" wire:model="url" placeholder="https://example.com">
                    @error('url') <p class="error">{{ $message }}</p> @enderror

                    <label class="mt-3 block">Facebook URL</label>
                    <input type="url" wire:model="facebookUrl" placeholder="https://facebook.com/...">
                    @error('facebookUrl') <p class="error">{{ $message }}</p> @enderror
                    @error('url') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div style="margin-top: 1rem;">
                    <label>what3words</label>
                    <p class="hint venue-wizard-w3w-hint">Three-word location for the car park or fishery entrance, e.g. <code>filled.count.soap</code></p>
                    <input type="text" wire:model.blur="what3words" placeholder="filled.count.soap">
                    @error('what3words') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div style="margin-top: 1rem;">
                    <label>Directions / parking</label>
                    <textarea wire:model="directions" rows="5" placeholder="How to find the car park, access tracks, gate codes if appropriate…"></textarea>
                    @error('directions') <p class="error">{{ $message }}</p> @enderror
                </div>
                <button type="button" wire:click="reverseGeocode" class="venue-wizard-btn-link" style="margin-top: 0.75rem;">
                    Refresh address from map pin
                </button>

                @unless ($editRequest)
                    <div class="venue-wizard-photos">
                        <label class="venue-wizard-photos-label">Venue photos</label>
                        <p class="hint">Upload photos of the lake, car park, pegs or signage (up to 8).</p>

                        @if ($this->existingPhotos->isNotEmpty() || count($newPhotos))
                            <div class="venue-wizard-photo-grid">
                                @foreach ($this->existingPhotos as $photo)
                                    <figure wire:key="existing-photo-{{ $photo->id }}" class="venue-wizard-photo-card">
                                        <img src="{{ $photo->url() }}" alt="Venue photo">
                                        <button type="button" wire:click="removeExistingPhoto({{ $photo->id }})" class="venue-wizard-photo-remove">Remove</button>
                                    </figure>
                                @endforeach
                                @foreach ($newPhotos as $index => $photo)
                                    <figure wire:key="new-photo-{{ $index }}" class="venue-wizard-photo-card is-new">
                                        <img src="{{ $photo->temporaryUrl() }}" alt="New venue photo">
                                        <button type="button" wire:click="removeNewPhoto({{ $index }})" class="venue-wizard-photo-remove">Remove</button>
                                    </figure>
                                @endforeach
                            </div>
                        @endif

                        <div class="venue-wizard-upload" wire:loading.class="is-loading" wire:target="newPhotos">
                            <input type="file"
                                   id="venue-wizard-photo-upload"
                                   wire:model="newPhotos"
                                   accept="image/*"
                                   multiple
                                   class="venue-wizard-upload-input">
                            <label for="venue-wizard-photo-upload" class="venue-wizard-upload-label">
                                <span class="venue-wizard-upload-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                </span>
                                <span class="venue-wizard-upload-title">Choose photos or drag here</span>
                                <span class="venue-wizard-upload-hint">JPG, PNG or WebP · max 5 MB each · up to 8 images</span>
                            </label>
                            <p wire:loading wire:target="newPhotos" class="venue-wizard-upload-status">Uploading…</p>
                        </div>
                        @error('newPhotos') <p class="error">{{ $message }}</p> @enderror
                        @error('newPhotos.*') <p class="error">{{ $message }}</p> @enderror
                    </div>
                @endunless
            </div>
        @endif

        @if ($step === 5)
            <div wire:key="step-details">
                <h2>{{ $editRequest ? 'Venue details & waters' : 'Tickets, waters & local knowledge' }}</h2>
                <p class="hint">{{ $editRequest ? 'Proposed changes to venue info and waters. Tactics, sessions and match reports are not included.' : 'Everything else anglers need before they visit.' }}</p>

                @if ($admin && ! $editRequest)
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
                @unless ($editRequest)
                    <div style="margin-top: 1rem;">
                        <label>Tactics &amp; local knowledge</label>
                        <textarea wire:model="tactics_guide" rows="4"></textarea>
                    </div>
                @endunless

                <div style="margin-top: 1rem;">
                    <label>Facilities</label>
                    <p class="hint">Tick everything available at this venue.</p>
                    <div class="check-grid">
                        @foreach (\App\Models\Venue::FACILITIES as $facilityKey => $facilityLabel)
                            <label>
                                <input type="checkbox"
                                       wire:model="facilities"
                                       value="{{ $facilityKey }}">
                                {{ $facilityLabel }}
                            </label>
                        @endforeach
                    </div>
                </div>

                @if ($editRequest)
                    <div style="margin-top: 1rem;">
                        <label>Note for reviewers (optional)</label>
                        <textarea wire:model="editRequestMessage" rows="3" placeholder="Explain what you changed and why…"></textarea>
                        @error('editRequestMessage') <p class="error">{{ $message }}</p> @enderror
                    </div>
                @endif

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

                            <div style="margin-top: 0.75rem;">
                                <label>Pond map image (top-down)</label>
                                <p class="hint">Used to place pegs. Upload a clear overhead view of the pond.</p>
                                @if (! empty($water['map_image_url']) && empty($water['map_image']))
                                    <img src="{{ $water['map_image_url'] }}" alt="" style="max-width: 100%; max-height: 12rem; object-fit: contain; border: 1px solid var(--vw-border); border-radius: 0.4rem; margin-bottom: 0.5rem; background: #f1f5f9;">
                                @endif
                                <input type="file" wire:model="waters.{{ $index }}.map_image" accept="image/*">
                                <div wire:loading wire:target="waters.{{ $index }}.map_image" class="hint">Uploading map…</div>
                                @error('waters.'.$index.'.map_image') <p class="error">{{ $message }}</p> @enderror
                            </div>

                            <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--vw-border);">
                                <div class="row" style="justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <strong>Pegs</strong>
                                    <button type="button" wire:click="addPeg({{ $index }})" class="venue-wizard-btn venue-wizard-btn-outline">Add peg</button>
                                </div>
                                <p class="hint">Give each peg a number and/or name, then click the pond map to place it.</p>

                                @php
                                    $mapPreviewUrl = null;
                                    if (! empty($water['map_image']) && $water['map_image'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                        try {
                                            $mapPreviewUrl = $water['map_image']->temporaryUrl();
                                        } catch (\Throwable) {
                                            $mapPreviewUrl = null;
                                        }
                                    }
                                    $mapPreviewUrl = $mapPreviewUrl ?: ($water['map_image_url'] ?? null);
                                @endphp

                                @foreach (($water['pegs'] ?? []) as $pegIndex => $peg)
                                    <div class="venue-wizard-water" style="margin-top: 0.75rem; background: var(--vw-bg);" wire:key="water-{{ $index }}-peg-{{ $pegIndex }}">
                                        <div class="row" style="justify-content: space-between; align-items: center;">
                                            <strong>Peg {{ $pegIndex + 1 }}</strong>
                                            <button type="button" wire:click="removePeg({{ $index }}, {{ $pegIndex }})" class="venue-wizard-btn-danger">Remove</button>
                                        </div>
                                        <div class="grid-2" style="margin-top: 0.5rem;">
                                            <div>
                                                <label>Number</label>
                                                <input type="text" wire:model="waters.{{ $index }}.pegs.{{ $pegIndex }}.number" placeholder="12">
                                            </div>
                                            <div>
                                                <label>Name</label>
                                                <input type="text" wire:model="waters.{{ $index }}.pegs.{{ $pegIndex }}.name" placeholder="Island">
                                            </div>
                                        </div>

                                        @if ($mapPreviewUrl)
                                            <div
                                                x-data="{
                                                    mapX: @js($peg['map_x'] !== null && $peg['map_x'] !== '' ? (float) $peg['map_x'] : null),
                                                    mapY: @js($peg['map_y'] !== null && $peg['map_y'] !== '' ? (float) $peg['map_y'] : null),
                                                    place(event) {
                                                        const rect = event.currentTarget.getBoundingClientRect();
                                                        if (! rect.width || ! rect.height) return;
                                                        this.mapX = Math.min(100, Math.max(0, ((event.clientX - rect.left) / rect.width) * 100));
                                                        this.mapY = Math.min(100, Math.max(0, ((event.clientY - rect.top) / rect.height) * 100));
                                                        $wire.setPegLocation({{ $index }}, {{ $pegIndex }}, this.mapX, this.mapY);
                                                    }
                                                }"
                                                style="margin-top: 0.75rem;"
                                            >
                                                <div @click="place($event)" style="position: relative; width: 100%; border: 2px solid var(--vw-border); border-radius: 0.5rem; overflow: hidden; background: #e2e8f0; cursor: crosshair;">
                                                    <img src="{{ $mapPreviewUrl }}" alt="Pond map" style="display: block; width: 100%; max-height: 14rem; object-fit: contain; margin: 0 auto; pointer-events: none;">
                                                    <span
                                                        x-show="mapX !== null && mapY !== null"
                                                        x-cloak
                                                        :style="mapX !== null && mapY !== null
                                                            ? `position:absolute;z-index:10;width:1.1rem;height:1.1rem;left:${mapX}%;top:${mapY}%;transform:translate(-50%,-50%);border-radius:9999px;border:2px solid #fff;background:#0369a1;box-shadow:0 1px 3px rgba(0,0,0,.4);pointer-events:none;`
                                                            : 'display:none'"
                                                    ></span>
                                                </div>
                                                <p class="hint" style="margin-top: 0.35rem;" x-show="mapX !== null && mapY !== null" x-cloak x-text="`Position ${Number(mapX).toFixed(1)}%, ${Number(mapY).toFixed(1)}%`"></p>
                                            </div>
                                        @else
                                            <p class="hint" style="margin-top: 0.75rem;">Upload a pond map image above before placing this peg.</p>
                                        @endif

                                        <div style="margin-top: 0.75rem;">
                                            <label>Peg photos</label>
                                            <p class="hint">Optional. Up to 4 photos of this peg.</p>
                                            @if (! empty($peg['existing_photos']))
                                                <div class="check-grid" style="margin-bottom: 0.5rem;">
                                                    @foreach ($peg['existing_photos'] as $existingPhoto)
                                                        <div style="position: relative;">
                                                            <img src="{{ $existingPhoto['url'] }}" alt="" style="width: 100%; height: 5rem; object-fit: cover; border-radius: 0.4rem; border: 1px solid var(--vw-border);">
                                                            <button type="button"
                                                                    wire:click="removePegExistingPhoto({{ $index }}, {{ $pegIndex }}, {{ $existingPhoto['id'] }})"
                                                                    class="venue-wizard-btn-danger"
                                                                    style="margin-top: 0.25rem; width: 100%;">
                                                                Remove
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <input type="file"
                                                   wire:model="waters.{{ $index }}.pegs.{{ $pegIndex }}.new_photos"
                                                   accept="image/*"
                                                   multiple>
                                            <div wire:loading wire:target="waters.{{ $index }}.pegs.{{ $pegIndex }}.new_photos" class="hint">Uploading…</div>
                                            @error('waters.'.$index.'.pegs.'.$pegIndex.'.new_photos.*') <p class="error">{{ $message }}</p> @enderror
                                            @error('waters.'.$index.'.pegs.'.$pegIndex.'.new_photos') <p class="error">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                @endforeach
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
                    @if ($editRequest)
                        Submit edit for approval
                    @else
                        {{ $venueId ? 'Save changes' : ($admin ? 'Create venue' : 'Submit for approval') }}
                    @endif
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
