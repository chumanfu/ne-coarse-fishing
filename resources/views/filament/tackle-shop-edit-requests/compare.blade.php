<x-filament-panels::page>
    @php
        $record = $this->getRecord();
    @endphp

    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm dark:border-gray-700 dark:bg-gray-900">
            <p><strong>Suggested by:</strong> {{ $record->user->name }} ({{ $record->user->email }})</p>
            @if ($record->message)
                <p class="mt-2"><strong>Note:</strong> {{ $record->message }}</p>
            @endif
            <p class="mt-2 capitalize"><strong>Status:</strong> {{ $record->status }}</p>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left">Field</th>
                        <th class="px-4 py-3 text-left">Current</th>
                        <th class="px-4 py-3 text-left">Proposed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->comparison as $row)
                        <tr @class(['bg-amber-50 dark:bg-amber-950/30' => $row['changed'], 'border-t border-gray-100 dark:border-gray-800'])>
                            <td class="px-4 py-3 font-semibold">{{ $row['label'] }}</td>
                            <td class="px-4 py-3 whitespace-pre-line">{{ $row['before'] ?: '—' }}</td>
                            <td class="px-4 py-3 whitespace-pre-line">{{ $row['after'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
