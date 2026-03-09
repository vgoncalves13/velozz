<x-filament-panels::page>
    <div class="space-y-6">
        @if(session('meta_oauth_success'))
            <x-filament::section>{{ session('meta_oauth_success') }}</x-filament::section>
        @endif
        @if(session('meta_oauth_error'))
            <x-filament::section>{{ session('meta_oauth_error') }}</x-filament::section>
        @endif
        {{-- Connected Accounts --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('meta_settings.accounts.title') }}</h2>
            </div>

            @forelse($this->getAccounts() as $account)
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 last:border-0 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <x-channel-icon :channel="$account->type" class="w-8 h-8" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $account->page_name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $account->type instanceof \App\Enums\Channel ? ucfirst(str_replace('_', ' ', $account->type->value)) : $account->type }}
                                &middot; Page ID: {{ $account->page_id }}
                            </p>
                        </div>
                        <span @class([
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' => $account->status === 'connected',
                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' => $account->status !== 'connected',
                        ])>
                            {{ ucfirst($account->status) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            wire:click="disconnect({{ $account->id }})"
                            wire:confirm="{{ __('meta_settings.accounts.disconnect_confirm') }}"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors"
                        >
                            {{ __('meta_settings.accounts.disconnect') }}
                        </button>
                        <button
                            wire:click="delete({{ $account->id }})"
                            wire:confirm="{{ __('meta_settings.accounts.delete_confirm') }}"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                        >
                            {{ __('meta_settings.accounts.delete') }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">{{ __('meta_settings.accounts.empty') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
