<x-filament-panels::page>
    <div class="flex gap-6 h-[calc(100vh-12rem)]">
        {{-- Conversations List --}}
        <div class="w-96 flex-shrink-0 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Conversations</h2>
            </div>

            <div class="flex-1 overflow-y-auto">
                @forelse($this->getLeadsWithMessages() as $lead)
                    <button
                        wire:click="selectConversation({{ $lead->id }})"
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
                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate mb-1">
                                    {{ $lead->whatsappMessages->first()?->content ?? 'No messages' }}
                                </p>
                                <span class="text-xs text-gray-500 dark:text-gray-500">
                                    {{ $lead->whatsappMessages->first()?->created_at?->diffForHumans() }}
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
                            <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-white">No conversations</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Get started by sending a message to a lead.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Conversation Area --}}
        <div class="flex-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if($selectedLeadId)
                @livewire('inbox-conversation', ['leadId' => $selectedLeadId], key($selectedLeadId))
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center px-8">
                        <svg class="mx-auto h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        <h3 class="mt-6 text-base font-semibold text-gray-900 dark:text-white">Select a conversation</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">Choose a conversation from the list to start messaging.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
