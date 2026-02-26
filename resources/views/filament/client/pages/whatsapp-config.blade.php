<x-filament-panels::page>
    <div class="space-y-6">
        @if(! $instance)
            {{-- No instance yet --}}
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No WhatsApp instance</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a WhatsApp instance.</p>
            </div>
        @else
            {{-- Status Card --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Connection Status</h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($instance->status === 'connected') bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400
                            @elseif($instance->status === 'connecting') bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400
                            @elseif($instance->status === 'error') bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-400
                            @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400
                            @endif">
                            {{ ucfirst($instance->status) }}
                        </span>
                    </div>

                    @if($instance->phone_number)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Phone:</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $instance->phone_number }}</span>
                        </div>
                    @endif

                    @if($instance->last_connected_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Last Connected:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $instance->last_connected_at->diffForHumans() }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Instance ID:</span>
                        <code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $instance->instance_id }}</code>
                    </div>
                </div>
            </div>

            {{-- QR Code Card --}}
            @if($instance->qr_code && $instance->status === 'connecting')
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">📱 Scan QR Code</h3>
                    <div class="text-center">
                        <img src="{{ $instance->qr_code }}" alt="QR Code" class="mx-auto w-64 h-64 border-4 border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            Open WhatsApp on your phone → Settings → Linked Devices → Link a Device
                        </p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                            After scanning, click "Check Status" button above
                        </p>
                    </div>
                </div>
            @endif

            {{-- Instructions --}}
            @if($instance->status === 'disconnected')
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">📋 How to Connect</h4>
                    <ol class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-decimal list-inside">
                        <li>Click "Connect WhatsApp" button above</li>
                        <li>Scan the QR code with your WhatsApp</li>
                        <li>Click "Check Status" to confirm connection</li>
                        <li>Start sending messages!</li>
                    </ol>
                </div>
            @endif

            @if($instance->isConnected())
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-green-900 dark:text-green-100 mb-2">✅ Connected Successfully!</h4>
                    <p class="text-sm text-green-800 dark:text-green-200">
                        Your WhatsApp is connected and ready to send messages to your leads.
                        You can now use templates and send messages from the lead details page.
                    </p>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
