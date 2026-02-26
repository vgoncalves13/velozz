<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Instructions --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">📊 How to Import Leads</h3>
            <ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-decimal list-inside">
                <li>Click "Upload File" button above</li>
                <li>Select your Excel (.xlsx, .xls) or CSV file</li>
                <li>File will be processed in background</li>
                <li>You'll see results below when complete</li>
            </ol>
        </div>

        {{-- Import History --}}
        <div>
            <h3 class="text-lg font-semibold mb-4">Recent Imports</h3>

            @php
                $imports = \App\Models\Import::query()
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->latest()
                    ->take(10)
                    ->get();
            @endphp

            @if($imports->isEmpty())
                <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No imports yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by uploading your first file.</p>
                </div>
            @else
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">File</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Status</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Results</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                            @foreach($imports as $import)
                                <tr>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ basename($import->filename) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @if($import->status === 'completed')
                                            <span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-900/20 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400">
                                                ✓ Completed
                                            </span>
                                        @elseif($import->status === 'processing')
                                            <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/20 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-400">
                                                ⏳ Processing
                                            </span>
                                        @elseif($import->status === 'failed')
                                            <span class="inline-flex items-center rounded-md bg-red-50 dark:bg-red-900/20 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-400">
                                                ✗ Failed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-gray-50 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-400">
                                                ⏸ Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        @if($import->status === 'completed')
                                            <span class="text-green-600 dark:text-green-400">✓ {{ $import->imported }}</span> /
                                            <span class="text-yellow-600 dark:text-yellow-400">⊘ {{ $import->duplicated }}</span> /
                                            <span class="text-red-600 dark:text-red-400">✗ {{ $import->errors }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $import->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
