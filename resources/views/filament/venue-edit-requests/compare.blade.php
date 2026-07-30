<x-filament-panels::page>
    @php
        /** @var \App\Models\VenueEditRequest $record */
        $record = $this->record;
    @endphp

    <style>
        .vec { display: flex; flex-direction: column; gap: 1.5rem; }
        .vec-meta {
            display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem;
            padding: 1.25rem 1.5rem; border-radius: 0.75rem;
            background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
        }
        .vec-meta p { margin: 0; font-size: 0.875rem; line-height: 1.5; color: rgba(255,255,255,.7); }
        .vec-meta strong { color: #fff; }
        .vec-badge {
            display: inline-flex; padding: 0.25rem 0.625rem; border-radius: 0.375rem;
            font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;
        }
        .vec-badge--pending { background: #78350f; color: #fef3c7; border: 1px solid #b45309; }
        .vec-badge--approved { background: #064e3b; color: #d1fae5; border: 1px solid #059669; }
        .vec-badge--rejected { background: #7f1d1d; color: #fee2e2; border: 1px solid #dc2626; }
        .vec-panel {
            border: 1px solid rgba(255,255,255,.12); border-radius: 0.75rem; overflow: hidden;
            background: rgba(255,255,255,.03);
        }
        .vec-panel-title {
            padding: 0.875rem 1.25rem; font-weight: 700; color: #fff;
            border-bottom: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.04);
        }
        .vec-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .vec-table col.vec-col-field { width: 12rem; }
        .vec-table col.vec-col-value { width: calc((100% - 12rem) / 2); }
        .vec-table th, .vec-table td {
            padding: 0.875rem 1rem; vertical-align: top; text-align: left;
            border-bottom: 1px solid rgba(255,255,255,.08); font-size: 0.875rem; line-height: 1.5;
        }
        .vec-table thead th {
            font-size: 0.8125rem; font-weight: 700; color: #fff;
            background: rgba(255,255,255,.06); border-bottom: 1px solid rgba(255,255,255,.14);
        }
        .vec-table thead th.vec-head-before { background: rgba(100,116,139,.35); }
        .vec-table thead th.vec-head-after { background: rgba(14,165,233,.22); }
        .vec-table tbody th {
            font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.04em;
            text-transform: uppercase; color: rgba(255,255,255,.5); background: rgba(255,255,255,.02);
        }
        .vec-table tbody td { color: rgba(255,255,255,.92); white-space: pre-line; word-break: break-word; }
        .vec-table tbody tr.vec-changed { background: rgba(245,158,11,.12); }
        .vec-table tbody tr.vec-changed th { color: #fcd34d; }
        .vec-table tbody tr:last-child th, .vec-table tbody tr:last-child td { border-bottom: none; }
        .vec-water { border-top: 1px solid rgba(255,255,255,.1); }
        .vec-water-head {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem;
            padding: 0.75rem 1.25rem; background: rgba(255,255,255,.04);
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .vec-water-head strong { color: #fff; font-size: 0.9375rem; }
        .vec-tag { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
        .vec-tag--added { color: #6ee7b7; }
        .vec-tag--removed { color: #fca5a5; }
        .vec-tag--modified { color: #fcd34d; }
        .vec-empty { padding: 1.25rem; color: rgba(255,255,255,.55); font-size: 0.875rem; }
        @media (max-width: 960px) {
            .vec-table, .vec-table thead, .vec-table tbody, .vec-table tr, .vec-table th, .vec-table td { display: block; width: 100%; }
            .vec-table thead { display: none; }
            .vec-table tbody th { border-bottom: none; padding-bottom: 0.25rem; }
            .vec-table tbody td { padding-top: 0.25rem; border-bottom: 1px solid rgba(255,255,255,.08); }
            .vec-table tbody td::before {
                display: block; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
                letter-spacing: 0.04em; color: rgba(255,255,255,.45); margin-bottom: 0.25rem;
            }
            .vec-table tbody td:nth-child(2)::before { content: 'Before (current)'; }
            .vec-table tbody td:nth-child(3)::before { content: 'After (proposed)'; }
        }
    </style>

    <div class="vec">
        <div class="vec-meta">
            <div>
                <p>
                    Submitted by <strong>{{ $record->user->name }}</strong>
                    · {{ $record->created_at->format('d M Y H:i') }}
                </p>
                @if ($record->message)
                    <p style="margin-top:0.5rem;">{{ $record->message }}</p>
                @endif
            </div>
            <span @class([
                'vec-badge',
                'vec-badge--pending' => $record->status === 'pending',
                'vec-badge--approved' => $record->status === 'approved',
                'vec-badge--rejected' => $record->status === 'rejected',
            ])>{{ $record->status }}</span>
        </div>

        <div class="vec-panel">
            <div class="vec-panel-title">Venue details</div>
            <table class="vec-table">
                <colgroup>
                    <col class="vec-col-field">
                    <col class="vec-col-value">
                    <col class="vec-col-value">
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">Field</th>
                        <th scope="col" class="vec-head-before">Before (current)</th>
                        <th scope="col" class="vec-head-after">After (proposed)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($comparison['fields'] as $field)
                        <tr @class(['vec-changed' => $field['changed']])>
                            <th scope="row">{{ $field['label'] }}</th>
                            <td>{{ $field['before'] }}</td>
                            <td>{{ $field['after'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="vec-panel">
            <div class="vec-panel-title">Waters / ponds</div>
            @forelse ($comparison['waters'] as $water)
                <div class="vec-water">
                    <div class="vec-water-head">
                        <strong>{{ $water['label'] }}</strong>
                        @if ($water['status'] === 'added')
                            <span class="vec-tag vec-tag--added">Added</span>
                        @elseif ($water['status'] === 'removed')
                            <span class="vec-tag vec-tag--removed">Removed</span>
                        @elseif ($water['changed'])
                            <span class="vec-tag vec-tag--modified">Modified</span>
                        @endif
                    </div>
                    <table class="vec-table">
                        <colgroup>
                            <col class="vec-col-field">
                            <col class="vec-col-value">
                            <col class="vec-col-value">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">Field</th>
                                <th scope="col" class="vec-head-before">Before (current)</th>
                                <th scope="col" class="vec-head-after">After (proposed)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr @class(['vec-changed' => $water['changed']])>
                                <th scope="row">Details</th>
                                <td>{{ $water['before'] }}</td>
                                <td>{{ $water['after'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @empty
                <p class="vec-empty">No waters listed.</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
