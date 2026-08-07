<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wide bg-slate-100 border border-slate-400 px-2 py-1 rounded">{{ $venue->ticketTypeLabel() }}</span>
                    @if ($venue->is_complex)
                        <span class="text-xs font-bold uppercase tracking-wide bg-amber-50 border border-amber-500 text-amber-950 px-2 py-1 rounded">Complex</span>
                    @endif
                    @if ($venue->manager_verified)
                        <span class="text-xs font-bold uppercase tracking-wide bg-emerald-100 border border-emerald-600 text-emerald-900 px-2 py-1 rounded">Manager verified</span>
                    @endif
                    @unless ($venue->is_approved)
                        <span class="text-xs font-bold uppercase tracking-wide bg-orange-100 border border-orange-600 text-orange-950 px-2 py-1 rounded">Pending approval</span>
                    @endunless
                </div>
                <h1 class="text-3xl font-bold text-slate-900">{{ $venue->name }}</h1>
                @if ($venue->address)
                    <p class="text-slate-600 mt-1">{{ $venue->address }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                @auth
                    <form method="POST" action="{{ $isFavourited ? route('venues.favourite.destroy', $venue) : route('venues.favourite.store', $venue) }}">
                        @csrf
                        @if ($isFavourited)
                            @method('DELETE')
                        @endif
                        <button type="submit" class="px-3 py-2 rounded-md border-2 font-semibold text-sm {{ $isFavourited ? 'border-amber-600 bg-amber-50 text-amber-950' : 'border-slate-800' }}">
                            {{ $isFavourited ? '★ Favourited' : '☆ Favourite' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-md border-2 border-slate-800 font-semibold text-sm">☆ Favourite</a>
                @endauth
                @if ($venue->url)
                    <a href="{{ $venue->url }}" target="_blank" rel="noopener noreferrer" class="px-3 py-2 rounded-md border-2 border-slate-800 font-semibold text-sm">Visit website</a>
                @endif
                @if ($venue->facebook_url)
                    <a href="{{ $venue->facebook_url }}" target="_blank" rel="noopener noreferrer" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm">Facebook</a>
                @endif
                <a href="{{ route('map.index', ['species' => null]) }}#venue-{{ $venue->id }}" class="px-3 py-2 rounded-md border-2 border-slate-800 font-semibold text-sm">View on map</a>
                @auth
                    <a href="{{ route('sessions.create', ['venue' => $venue->slug]) }}" class="px-3 py-2 rounded-md bg-sky-700 text-white font-semibold text-sm">Log a session here</a>
                    @can('suggestEdit', $venue)
                        <a href="{{ route('venues.suggest-edit', $venue) }}" class="px-3 py-2 rounded-md border-2 border-amber-600 text-amber-950 font-semibold text-sm">Suggest an edit</a>
                    @endcan
                    @can('manage', $venue)
                        <a href="{{ route('venues.edit', $venue) }}" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm">Edit venue</a>
                        <a href="{{ route('pegs.create', $venue) }}" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm">Add peg</a>
                        <a href="{{ route('match-reports.create', $venue) }}" class="px-3 py-2 rounded-md border-2 border-emerald-700 text-emerald-900 font-semibold text-sm">Match report</a>
                        <a href="{{ route('announcements.create', $venue) }}" class="px-3 py-2 rounded-md border-2 border-emerald-700 text-emerald-900 font-semibold text-sm">Announcement</a>
                    @elsecan('claim', $venue)
                        <form method="POST" action="{{ route('venues.claim', $venue) }}" class="inline">
                            @csrf
                            <button class="px-3 py-2 rounded-md border-2 border-amber-600 text-amber-950 font-semibold text-sm" onclick="return confirm('Claim management of this venue?')">Claim ownership</button>
                        </form>
                    @endcan
                    @if (auth()->user() && $venue->canManagePegs(auth()->user()) && ! auth()->user()->can('manage', $venue))
                        <a href="{{ route('pegs.create', $venue) }}" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm">Add peg</a>
                    @endif
                @endauth
            </div>
        </div>
        <div class="mt-4">
            <x-share-links
                :url="route('venues.show', $venue)"
                :title="$venue->name"
                :text="'Check out '.$venue->name.' on '.config('app.name')"
                label="Share venue"
            />
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="text-xl font-bold mb-3">Overview</h2>
                <p class="text-slate-800 whitespace-pre-line">{{ $venue->overview ?: 'No overview provided yet.' }}</p>
            </section>

            @if ($venue->clubs->isNotEmpty())
                <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                    <h2 class="text-xl font-bold mb-3">Managed by / club access</h2>
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($venue->clubs as $club)
                            <li>
                                <a href="{{ route('clubs.show', $club) }}" class="inline-flex text-sm font-semibold bg-sky-50 border border-sky-300 text-sky-900 px-3 py-1.5 rounded hover:bg-sky-100">
                                    {{ $club->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($venue->photos->isNotEmpty())
                <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-xl font-bold">Photos</h2>
                        @can('manage', $venue)
                            <a href="{{ route('venues.edit', $venue) }}" class="text-sm font-semibold text-sky-800 hover:underline">Manage photos</a>
                        @endcan
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($venue->photos as $photo)
                            <a href="{{ $photo->url() }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $photo->url() }}" alt="{{ $venue->name }} photo" class="rounded-lg object-cover h-40 w-full border border-slate-300 hover:border-sky-700">
                            </a>
                        @endforeach
                    </div>
                </section>
            @elseif (auth()->user()?->can('manage', $venue))
                <section class="bg-white border-2 border-dashed border-slate-300 rounded-xl p-5">
                    <h2 class="text-xl font-bold mb-2">Photos</h2>
                    <p class="text-slate-600 text-sm mb-3">Add multiple venue photos (car park, lakes, facilities) from the venue editor.</p>
                    <a href="{{ route('venues.edit', $venue) }}" class="inline-flex text-sm font-semibold text-sky-800 hover:underline">Upload photos</a>
                </section>
            @endif

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="text-xl font-bold mb-4">Waters</h2>
                <div class="space-y-4">
                    @foreach ($venue->waters as $water)
                        @php
                            $waterPegs = $water->pegs
                                ->where('is_verified', true)
                                ->filter(fn ($peg) => $peg->latitude !== null && $peg->longitude !== null)
                                ->values();
                            $waterPegsPayload = $waterPegs->map(function ($peg) {
                                return [
                                    'id' => $peg->id,
                                    'label' => $peg->label(),
                                    'lat' => $peg->latitude,
                                    'lng' => $peg->longitude,
                                    'description' => $peg->description,
                                ];
                            })->values();
                        @endphp
                        <div class="border-2 border-slate-200 rounded-lg p-4">
                            <h3 class="font-bold text-slate-900">{{ $water->name }}</h3>
                            @if ($water->description)
                                <p class="text-sm text-slate-700 mt-1 whitespace-pre-line">{{ $water->description }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-3 text-sm text-slate-700">
                                @if ($water->peg_count)
                                    <span><strong>Pegs:</strong> {{ $water->peg_count }}</span>
                                @endif
                                @if ($water->depth_info)
                                    <span><strong>Depth:</strong> {{ $water->depth_info }}</span>
                                @endif
                            </div>
                            @if ($water->species->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($water->species as $species)
                                        <span class="text-xs font-semibold bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded">{{ $species->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <x-water-facilities :water="$water" />
                            @if ($waterPegs->isNotEmpty())
                                <div class="mt-4">
                                    <p class="text-sm font-semibold text-slate-800 mb-2">Peg map</p>
                                    <div
                                        id="water-map-{{ $water->id }}"
                                        class="w-full rounded-lg border-2 border-slate-400 overflow-hidden bg-slate-200"
                                        style="height: 16rem; min-height: 16rem;"
                                        data-pegs='@json($waterPegsPayload)'
                                    ></div>
                                    <p class="text-xs text-slate-500 mt-2">{{ $waterPegs->count() }} mapped peg{{ $waterPegs->count() === 1 ? '' : 's' }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                    <h2 id="tactics" class="text-xl font-bold">Tactics &amp; local knowledge</h2>
                    @auth
                        <div class="flex flex-wrap gap-2 shrink-0">
                            <a href="{{ route('tactics.create', $venue) }}" class="px-3 py-2 rounded-md bg-sky-700 text-white font-semibold text-sm hover:bg-sky-800">
                                Share a tactic
                            </a>
                            <a href="{{ route('sessions.create', ['venue' => $venue->slug]) }}" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm hover:bg-sky-50">
                                Log a session
                            </a>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="shrink-0 px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 font-semibold text-sm hover:bg-sky-50">
                            Log in to share tactics
                        </a>
                    @endauth
                </div>

                @if ($venue->tactics_guide)
                    <div class="mb-6">
                        <h3 class="font-bold text-slate-900 mb-2">Official guide</h3>
                        <p class="text-slate-800 whitespace-pre-line">{{ $venue->tactics_guide }}</p>
                    </div>
                @endif

                @if ($anglerTactics->isNotEmpty())
                    <div class="@if($venue->tactics_guide) border-t-2 border-slate-200 pt-6 @endif">
                        <h3 class="font-bold text-slate-900 mb-3">Angler tips</h3>
                        <div class="space-y-4">
                            @foreach ($anglerTactics as $tactic)
                                <article class="border-2 border-slate-200 rounded-lg p-4">
                                    <p class="text-slate-800 whitespace-pre-line">{{ $tactic->body }}</p>
                                    <div class="flex flex-wrap items-center justify-between gap-2 mt-2">
                                        <p class="text-sm text-slate-600">
                                            {{ $tactic->user->name }}
                                            @if ($tactic->fished_at)
                                                · {{ $tactic->fished_at->format('d M Y') }}
                                            @endif
                                            @if ($tactic->water)
                                                · {{ $tactic->water->name }}
                                            @endif
                                            @if ($tactic->peg_number)
                                                · Peg {{ $tactic->peg_number }}
                                            @endif
                                            @if ($tactic->fishing_session_id)
                                                · <span class="text-slate-500">from a session log</span>
                                            @endif
                                        </p>
                                        @auth
                                            @can('update', $tactic)
                                                <a href="{{ route('tactics.edit', $tactic) }}" class="text-sm font-semibold text-sky-800 hover:underline">Edit</a>
                                            @endcan
                                        @endauth
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @elseif (! $venue->tactics_guide)
                    <p class="text-slate-600">No tactics guide yet — share what worked on your visit.</p>
                @endif
            </section>

            <section id="official" class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <h2 class="text-xl font-bold mb-1">Official announcements &amp; match reports</h2>
                <p class="text-sm text-slate-600 mb-4">Posted by verified fishery managers and club admins.</p>

                <div class="space-y-4">
                    @forelse ($venue->announcements->concat($venue->matchReports)->sortByDesc('published_at') as $item)
                        <article class="border-l-4 border-emerald-600 pl-4 py-1">
                            <div class="flex flex-wrap gap-2 items-center mb-1">
                                <span class="text-xs font-bold uppercase tracking-wide text-emerald-800">
                                    {{ $item instanceof \App\Models\MatchReport ? 'Match Report' : $item->typeLabel() }}
                                </span>
                                <span class="text-xs text-slate-500">{{ optional($item->published_at)->format('d M Y') }}</span>
                            </div>
                            <h3 class="font-bold text-slate-900">{{ $item->title }}</h3>
                            <p class="text-slate-800 mt-1 whitespace-pre-line">{{ $item->body }}</p>
                        </article>
                    @empty
                        <p class="text-slate-600">No official posts yet.</p>
                    @endforelse
                </div>
            </section>

            @if (auth()->user() && $venue->canManagePegs(auth()->user()))
                @php
                    $officialPegs = $venue->waters->flatMap->pegs->where('is_verified', true)->values();
                    $pendingPegsList = $pendingPegs ?? collect();
                @endphp
                <section class="bg-white border-2 border-sky-400 rounded-xl p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                        <div>
                            <h2 class="text-xl font-bold mb-1">Manage pegs</h2>
                            <p class="text-sm text-slate-600">Verify angler suggestions or add official pegs for this fishery.</p>
                        </div>
                        <a href="{{ route('pegs.create', $venue) }}" class="shrink-0 px-3 py-2 rounded-md bg-sky-700 text-white text-sm font-semibold hover:bg-sky-800">Add peg</a>
                    </div>

                    @if ($pendingPegsList->isNotEmpty())
                        <h3 class="font-bold text-amber-950 mb-2">Awaiting verification</h3>
                        <div class="space-y-3 mb-6">
                            @foreach ($pendingPegsList as $peg)
                                <div class="flex flex-col gap-3 border-2 border-amber-300 rounded-lg p-3 bg-amber-50/50">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $peg->label() }} <span class="text-slate-500 font-normal">on {{ $peg->water?->name }}</span></p>
                                            <p class="text-xs text-slate-500">{{ number_format($peg->latitude, 5) }}, {{ number_format($peg->longitude, 5) }}</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('pegs.edit', [$venue, $peg]) }}" class="px-3 py-2 rounded-md border-2 border-sky-700 text-sky-900 text-sm font-semibold">Edit</a>
                                            <form method="POST" action="{{ route('pegs.verify', [$venue, $peg]) }}">
                                                @csrf
                                                <button class="px-3 py-2 rounded-md bg-sky-700 text-white text-sm font-semibold hover:bg-sky-800">Verify</button>
                                            </form>
                                            <form method="POST" action="{{ route('pegs.destroy', [$venue, $peg]) }}" onsubmit="return confirm('Remove this suggested peg?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-2 rounded-md border-2 border-slate-400 text-sm font-semibold">Reject</button>
                                            </form>
                                        </div>
                                    </div>
                                    @if ($peg->photos->isNotEmpty())
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                            @foreach ($peg->photos as $photo)
                                                <a href="{{ $photo->url() }}" target="_blank" rel="noopener noreferrer">
                                                    <img src="{{ $photo->url() }}" alt="{{ $peg->label() }} photo" class="rounded-md object-cover h-24 w-full border border-slate-300">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-600 mb-6">No pegs waiting for verification.</p>
                    @endif

                    <h3 class="font-bold text-slate-900 mb-2">Official pegs</h3>
                    @if ($officialPegs->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($officialPegs as $peg)
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 border-2 border-slate-200 rounded-lg px-3 py-2">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $peg->label() }} <span class="text-slate-500 font-normal">· {{ $peg->water?->name }}</span></p>
                                        <p class="text-xs text-slate-500">{{ number_format($peg->latitude, 5) }}, {{ number_format($peg->longitude, 5) }}@if($peg->photos->isNotEmpty()) · {{ $peg->photos->count() }} photo{{ $peg->photos->count() === 1 ? '' : 's' }}@endif</p>
                                        @if ($peg->description)
                                            <p class="text-sm text-slate-700 mt-2 whitespace-pre-line">{{ Str::limit($peg->description, 280) }}</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-3 shrink-0">
                                        <a href="{{ route('pegs.edit', [$venue, $peg]) }}" class="text-sm font-semibold text-sky-800 hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('pegs.destroy', [$venue, $peg]) }}" onsubmit="return confirm('Remove this official peg?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-semibold text-red-800 hover:underline">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-600">No official pegs yet. <a href="{{ route('pegs.create', $venue) }}" class="text-sky-800 font-semibold hover:underline">Add the first one</a>.</p>
                    @endif
                </section>
            @endif

            @php
                $verifiedPegs = $venue->waters->flatMap->pegs->where('is_verified', true)
                    ->filter(fn ($peg) => $peg->photos->isNotEmpty() || filled($peg->description))
                    ->values();
            @endphp
            @if ($verifiedPegs->isNotEmpty())
                <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                    <h2 class="text-xl font-bold mb-1">Pegs</h2>
                    <p class="text-sm text-slate-600 mb-4">Official peg write-ups and photos.</p>
                    <div class="space-y-6">
                        @foreach ($verifiedPegs as $peg)
                            <div class="border-2 border-slate-200 rounded-lg p-4">
                                <p class="font-semibold text-slate-900 mb-1">{{ $peg->label() }} <span class="text-slate-500 font-normal">· {{ $peg->water?->name }}</span></p>
                                @if ($peg->description)
                                    <p class="text-sm text-slate-700 whitespace-pre-line mb-3">{{ $peg->description }}</p>
                                @endif
                                @if ($peg->photos->isNotEmpty())
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        @foreach ($peg->photos as $photo)
                                            <a href="{{ $photo->url() }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ $photo->url() }}" alt="{{ $peg->label() }} photo" class="rounded-md object-cover h-28 w-full border border-slate-300 hover:border-sky-700">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold">Recent sessions</h2>
                </div>
                <div class="space-y-4">
                    @forelse ($venue->fishingSessions as $session)
                        <a href="{{ route('sessions.show', $session) }}" class="block border-2 border-slate-200 rounded-lg p-4 hover:border-sky-700 transition">
                            <div class="flex flex-wrap justify-between gap-2">
                                <p class="font-semibold text-slate-900">{{ $session->user->name }} · {{ $session->fished_at->format('d M Y') }}</p>
                                @if ($session->water || $session->pegLabel())
                                    <p class="text-sm text-slate-600">
                                        {{ $session->water?->name }}
                                        @if ($session->pegLabel())
                                            @if ($session->water) · @endif Peg {{ $session->pegLabel() }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            @if ($session->commentary)
                                <p class="text-sm text-slate-800 mt-2">{{ Str::limit($session->commentary, 220) }}</p>
                            @endif
                            @if ($session->catches->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($session->catches->take(4) as $catch)
                                        <span class="text-xs font-semibold bg-slate-100 border border-slate-300 px-2 py-1 rounded">
                                            {{ $catch->species->name }}
                                            @if ($catch->weight_lb) · {{ $catch->weight_lb }}lb @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <p class="mt-3 text-sm font-semibold text-sky-800">View session →</p>
                        </a>
                    @empty
                        <p class="text-slate-600">No sessions logged here yet.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <div id="venue-map" class="h-64 rounded-xl border-2 border-slate-400 overflow-hidden"></div>

            <section class="bg-white border-2 border-slate-300 rounded-xl p-5 space-y-4 text-sm">
                @if ($venue->url)
                    <div>
                        <h3 class="font-bold text-slate-900 mb-1">Website</h3>
                        <a href="{{ $venue->url }}" target="_blank" rel="noopener noreferrer" class="text-sky-800 underline break-all">{{ $venue->url }}</a>
                    </div>
                @endif
                @if ($venue->facebook_url)
                    <div>
                        <h3 class="font-bold text-slate-900 mb-1">Facebook</h3>
                        <a href="{{ $venue->facebook_url }}" target="_blank" rel="noopener noreferrer" class="text-sky-800 underline break-all">{{ $venue->facebook_url }}</a>
                    </div>
                @endif
                @if ($venue->what3words)
                    <div>
                        <h3 class="font-bold text-slate-900 mb-1">what3words</h3>
                        <a href="{{ $venue->what3wordsUrl() }}" target="_blank" rel="noopener noreferrer" class="text-sky-800 font-semibold hover:underline">{{ $venue->what3wordsLabel() }}</a>
                        <p class="text-xs text-slate-500 mt-1">Opens in the what3words app or website.</p>
                    </div>
                @endif
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Day tickets</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->day_ticket_info ?: 'Not listed.' }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Membership</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->membership_info ?: 'Not listed.' }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Opening times</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->opening_times ?: 'Not listed.' }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Seasonal restrictions</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->season_info ?: 'Not listed.' }}</p>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 mb-1">Directions / parking</h3>
                    <p class="text-slate-800 whitespace-pre-line">{{ $venue->directions ?: 'Not listed.' }}</p>
                </div>
                @if ($speciesList->isNotEmpty())
                    <div>
                        <h3 class="font-bold text-slate-900 mb-2">Species on site</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($speciesList as $species)
                                <span class="text-xs font-semibold bg-sky-50 border border-sky-300 text-sky-900 px-2 py-1 rounded">{{ $species->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </aside>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const map = L.map('venue-map').setView([{{ $venue->latitude }}, {{ $venue->longitude }}], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                L.marker([{{ $venue->latitude }}, {{ $venue->longitude }}]).addTo(map)
                    .bindPopup(@json($venue->name));

                document.querySelectorAll('[id^="water-map-"]').forEach((el) => {
                    let pegs = [];
                    try {
                        pegs = JSON.parse(el.dataset.pegs || '[]');
                    } catch (e) {
                        return;
                    }
                    if (!pegs.length) {
                        return;
                    }

                    const escapeHtml = (value) => String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;');

                    const waterMap = L.map(el.id);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(waterMap);

                    const bounds = [];
                    pegs.forEach((peg) => {
                        const marker = L.marker([peg.lat, peg.lng]).addTo(waterMap);
                        const popup = peg.description
                            ? `<strong>${escapeHtml(peg.label)}</strong><br>${escapeHtml(peg.description)}`
                            : `<strong>${escapeHtml(peg.label)}</strong>`;
                        marker.bindPopup(popup);
                        bounds.push([peg.lat, peg.lng]);
                    });

                    if (bounds.length === 1) {
                        waterMap.setView(bounds[0], 17);
                    } else {
                        waterMap.fitBounds(bounds, { padding: [24, 24], maxZoom: 18 });
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
