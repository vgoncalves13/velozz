<div class="rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden mb-6">
    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-300 dark:border-gray-600">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white">
            📊 Preview - First {{ count($rows) }} rows
        </h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
            Review your data before mapping columns
        </p>
    </div>

    <div class="overflow-x-auto max-h-96">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-900 sticky top-0">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-900">
                        #
                    </th>
                    @foreach($headers as $header)
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-950 divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($rows as $index => $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                        <td class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/30">
                            {{ $index + 1 }}
                        </td>
                        @foreach($headers as $headerIndex => $header)
                            <td class="px-3 py-2 text-sm font-normal text-gray-900 dark:text-gray-200 whitespace-nowrap">
                                {{ $row[$headerIndex] ?? '—' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(count($rows) >= 20)
        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 text-center border-t border-gray-300 dark:border-gray-600">
            <p class="text-xs text-gray-600 dark:text-gray-400">
                Showing first 20 rows. Your file may contain more data.
            </p>
        </div>
    @endif
</div>
