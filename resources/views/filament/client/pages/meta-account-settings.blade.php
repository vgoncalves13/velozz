<x-filament-panels::page>
    {{-- Field Mapping Modal --}}
    <x-filament::modal id="lead-form-field-mapping" width="lg">
        <x-slot name="heading">
            {{ __('meta_settings.lead_forms.mapping_title', ['name' => $mappingFormName]) }}
        </x-slot>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('meta_settings.lead_forms.mapping_name_field') }}
                </label>
                <select wire:model="mappedNameField" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">{{ __('meta_settings.lead_forms.mapping_no_field') }}</option>
                    @foreach($availableFormFields as $field)
                        <option value="{{ $field['key'] }}">{{ $field['label'] ?? $field['key'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('meta_settings.lead_forms.mapping_email_field') }}
                </label>
                <select wire:model="mappedEmailField" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">{{ __('meta_settings.lead_forms.mapping_no_field') }}</option>
                    @foreach($availableFormFields as $field)
                        <option value="{{ $field['key'] }}">{{ $field['label'] ?? $field['key'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('meta_settings.lead_forms.mapping_phone_field') }}
                </label>
                <select wire:model="mappedPhoneField" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">{{ __('meta_settings.lead_forms.mapping_no_field') }}</option>
                    @foreach($availableFormFields as $field)
                        <option value="{{ $field['key'] }}">{{ $field['label'] ?? $field['key'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex gap-3">
                <x-filament::button wire:click="saveMappingAndSync">
                    {{ __('meta_settings.lead_forms.save_and_sync') }}
                </x-filament::button>
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'lead-form-field-mapping' })">
                    {{ __('meta_settings.lead_forms.cancel') }}
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

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
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="flex items-center justify-between gap-4">
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

                    {{-- Lead Forms Section (only for connected Facebook pages) --}}
                    @if($account->type === \App\Enums\Channel::FacebookMessenger && $account->status === 'connected')
                        <div
                            x-data="{ open: false, forms: [], loading: false }"
                            x-init="$watch('open', value => {
                                if (value && forms.length === 0) {
                                    loading = true;
                                    $wire.getLeadForms({{ $account->id }}).then(result => {
                                        forms = result;
                                        loading = false;
                                    });
                                }
                            })"
                            class="mt-4 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden"
                        >
                            <button
                                @click="open = !open"
                                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/50 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <span class="flex items-center gap-2">
                                    <x-heroicon-o-document-text class="w-4 h-4" />
                                    {{ __('meta_settings.lead_forms.title') }}
                                </span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                            </button>

                            <div x-show="open" x-collapse>
                                <div x-show="loading" class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('meta_settings.lead_forms.loading') }}
                                </div>

                                <template x-if="!loading && forms.length === 0">
                                    <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('meta_settings.lead_forms.empty') }}
                                    </div>
                                </template>

                                <template x-for="form in forms" :key="form.id">
                                    <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="form.name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <span x-text="form.leads_count ?? 0"></span> {{ __('meta_settings.lead_forms.leads') }}
                                                &middot; <span x-text="form.status"></span>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <template x-if="form.subscribed">
                                                @foreach(\App\Models\FacebookLeadForm::where('meta_account_id', $account->id)->get() as $savedForm)
                                                    <span
                                                        x-show="form.id === '{{ $savedForm->form_id }}'"
                                                        class="text-xs text-gray-400 dark:text-gray-500"
                                                    >
                                                        @if($savedForm->last_synced_at)
                                                            {{ __('meta_settings.lead_forms.last_sync') }}: {{ $savedForm->last_synced_at->diffForHumans() }}
                                                            <button
                                                                wire:click="syncLeadForm({{ $savedForm->id }})"
                                                                class="ml-2 text-primary-600 dark:text-primary-400 hover:underline"
                                                            >
                                                                {{ __('meta_settings.lead_forms.sync_now') }}
                                                            </button>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </template>
                                            <button
                                                @click="$wire.toggleLeadForm({{ $account->id }}, form.id, form.name).then(() => { forms = []; $wire.getLeadForms({{ $account->id }}).then(r => forms = r); })"
                                                x-bind:class="form.subscribed ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600'"
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                                role="switch"
                                                x-bind:aria-checked="form.subscribed"
                                            >
                                                <span
                                                    x-bind:class="form.subscribed ? 'translate-x-5' : 'translate-x-0'"
                                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                ></span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center">
                    <p class="text-gray-500 dark:text-gray-400">{{ __('meta_settings.accounts.empty') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
