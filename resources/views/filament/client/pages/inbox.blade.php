<x-filament-panels::page>
    <div class="flex flex-col lg:flex-row gap-0 lg:gap-6 h-[calc(100dvh-17rem)]"
         x-data="{ mobileView: 'list' }">
        {{-- Conversations List --}}
        <div class="w-full lg:w-80 xl:w-96 lg:flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex-col"
             :class="mobileView === 'list' ? 'flex' : 'hidden lg:flex'">
            <div class="p-4 lg:p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg lg:text-xl font-semibold text-gray-900 dark:text-white">{{ __('inbox.labels.conversations') }}</h2>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div wire:loading.remove>
                @forelse($this->getLeadsWithMessages() as $lead)
                    @php
                        $lastWhatsapp = $lead->whatsappMessages->first();
                        $lastSocial = $lead->socialMessages->first();

                        // Pick whichever is more recent
                        if ($lastWhatsapp && $lastSocial) {
                            $lastMessage = $lastWhatsapp->created_at >= $lastSocial->created_at ? $lastWhatsapp : $lastSocial;
                        } else {
                            $lastMessage = $lastWhatsapp ?? $lastSocial;
                        }

                        $channel = $lead->last_message_channel ?? ($lastMessage ? ($lastMessage instanceof \App\Models\SocialMessage ? $lastMessage->channel : \App\Enums\Channel::Whatsapp) : \App\Enums\Channel::Whatsapp);
                    @endphp
                    <button
                        wire:click="selectConversation({{ $lead->id }})"
                        x-on:click="mobileView = 'conversation'"
                        @class([
                            'w-full text-left p-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700',
                            'bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800' => $selectedLeadId === $lead->id,
                        ])
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                <span class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                                    {{ substr($lead->full_name, 0, 1) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $lead->full_name }}
                                    </p>
                                    @if($lead->unread_count > 0)
                                        <span class="ml-2 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-danger-600 rounded-full">
                                            {{ $lead->unread_count }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <x-channel-icon :channel="$channel" class="w-3.5 h-3.5 flex-shrink-0" />
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                        {{ $lastMessage?->content ?? __('inbox.labels.no_messages') }}
                                    </p>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-500">
                                    {{ $lastMessage?->created_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="flex items-center justify-center h-full p-8">
                        <div class="text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-white">{{ __('inbox.empty_states.no_conversations_title') }}</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('inbox.empty_states.no_conversations_description') }}</p>
                            <a href="/app/leads" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ __('inbox.actions.view_leads') }}
                            </a>
                        </div>
                    </div>
                @endforelse
                </div>
            </div>
        </div>

        {{-- Conversation Area --}}
        <div class="flex-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex-col"
             :class="mobileView === 'conversation' ? 'flex' : 'hidden lg:flex'">
            {{-- Mobile back button --}}
            <div class="lg:hidden flex items-center px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <button x-on:click="mobileView = 'list'" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('inbox.labels.conversations') }}
                </button>
            </div>
            @if($selectedLeadId)
                @livewire('inbox-conversation', ['leadId' => $selectedLeadId], key($selectedLeadId))
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center px-8">
                        <svg class="mx-auto h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        <h3 class="mt-6 text-base font-semibold text-gray-900 dark:text-white">{{ __('inbox.empty_states.select_conversation_title') }}</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">{{ __('inbox.empty_states.select_conversation_description') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
