@php
 use Filament\Support\Icons\Heroicon;
@endphp
<div class="flex flex-col flex-1 min-h-0"
     wire:poll.3s.keep-alive
     x-data="{
         playNotification() {
             const audio = this.$refs.notificationSound;
             if (audio) {
                 audio.play().catch(() => {});
             }
         }
     }"
     @new-incoming-message.window="playNotification()">

    {{-- Notification Sound --}}
    <audio x-ref="notificationSound" preload="auto">
        <source src="{{ asset('sounds/notification-sound.wav') }}" type="audio/wav">
    </audio>

    {{-- Header with Lead Info and Actions --}}
    <div class="border-b border-gray-200 dark:border-gray-700 p-3 sm:p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    <span class="text-xl font-semibold text-primary-600 dark:text-primary-400">
                        {{ substr($lead->full_name, 0, 1) }}
                    </span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $lead->full_name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $lead->primary_whatsapp ?? $lead->first_whatsapp ?? __('inbox.labels.no_whatsapp') }}
                    </p>
                </div>
            </div>

            <div class="flex gap-1 sm:gap-2">
                @if(!$lead->assigned_user_id || $lead->assigned_user_id !== auth()->id())
                    <button
                        wire:click="assumeConversation"
                        class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                    >
                        <svg class="sm:mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="hidden sm:inline">{{ __('inbox.labels.assume') }}</span>
                    </button>
                @endif

                <button
                    wire:click="openTransferModal"
                    class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                >
                    <svg class="sm:mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('inbox.labels.transfer') }}</span>
                </button>

                <button
                    wire:click="openMergeModal"
                    class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                >
                    <svg class="sm:mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('inbox.labels.merge') }}</span>
                </button>

                <a
                    href="{{ $this->leadViewUrl }}"
                    class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                    target="_blank"
                >
                    <svg class="sm:mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('inbox.labels.view_lead') }}</span>
                </a>
            </div>
        </div>

        @if($lead->assigned_user_id)
            <div class="mt-4 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>{{ __('inbox.labels.assigned_to') }} <span class="font-medium text-gray-900 dark:text-white">{{ $lead->assignedUser?->name ?? __('inbox.labels.unknown') }}</span></span>
            </div>
        @endif
    </div>

    {{-- Messages Area --}}
    <div
        class="flex-1 overflow-y-auto p-3 sm:p-5 space-y-4 bg-gray-50 dark:bg-gray-900"
        x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }"
        x-init="scrollToBottom(); $watch('$wire.refreshKey', () => scrollToBottom())"
    >
        @forelse($this->messages as $message)
            @if($message->type === \App\Enums\MessageType::InternalNote)
                {{-- Internal Note --}}
                <div class="flex justify-center">
                    <div class="max-w-2xl px-4 py-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 text-yellow-900 dark:text-yellow-200 rounded-lg text-sm">
                        <p class="font-semibold mb-1">📝 {{ __('inbox.labels.internal_note') }}</p>
                        <p class="leading-relaxed">{{ $message->content }}</p>
                        <p class="text-xs mt-2 opacity-75">{{ $message->sentBy?->name }} - {{ $message->created_at->format('H:i') }}</p>
                    </div>
                </div>
            @else
                {{-- Regular Message --}}
                <div
                    data-message-id="{{ $message->id }}"
                    data-direction="{{ $message->direction->value }}"
                    @class([
                        'flex',
                        'justify-end' => $message->direction === \App\Enums\MessageDirection::Outgoing,
                        'justify-start' => $message->direction === \App\Enums\MessageDirection::Incoming,
                    ])>
                    <div @class([
                        'max-w-xl rounded-2xl shadow-sm overflow-hidden',
                        'bg-blue-100 dark:bg-blue-900/30 text-gray-900 dark:text-gray-100' => $message->direction === \App\Enums\MessageDirection::Outgoing,
                        'bg-green-100 dark:bg-green-900/30 text-gray-900 dark:text-gray-100' => $message->direction === \App\Enums\MessageDirection::Incoming,
                    ])>
                        {{-- Image Message --}}
                        @if($message->type === \App\Enums\MessageType::Image && $message->media_url)
                            <img src="{{ $message->media_url }}" alt="Image" class="w-full max-w-sm rounded-t-2xl">
                            @if($message->content)
                                <p class="text-sm leading-relaxed px-4 py-3">{{ $message->content }}</p>
                            @else
                                <div class="px-4 py-3"></div>
                            @endif
                        @elseif($message->type === \App\Enums\MessageType::Document && $message->media_url)
                            {{-- Document Message --}}
                            <a href="{{ $message->media_url }}" target="_blank" class="flex items-center gap-3 px-4 py-3 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                                <div class="shrink-0 w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ basename($message->media_url) }}</p>
                                    @if($message->content)
                                        <p class="text-xs opacity-75 mt-1">{{ $message->content }}</p>
                                    @endif
                                </div>
                                <svg class="shrink-0 h-5 w-5 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </a>
                        @else
                            {{-- Text Message --}}
                            <p class="text-sm leading-relaxed px-4 py-3">{{ $message->content }}</p>
                        @endif

                        <div @class([
                            'flex items-center justify-between text-xs gap-2 px-4 pb-3',
                            'text-gray-600 dark:text-gray-400' => $message->direction === \App\Enums\MessageDirection::Outgoing,
                            'text-gray-600 dark:text-gray-400' => $message->direction === \App\Enums\MessageDirection::Incoming,
                        ])>
                            <span>{{ $message->created_at->format('H:i') }}</span>

                            @if($message->direction === \App\Enums\MessageDirection::Outgoing)
                                <span class="flex items-center">
                                    @if($message->status === \App\Enums\MessageStatus::Sent)
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif($message->status === \App\Enums\MessageStatus::Delivered)
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg class="h-4 w-4 -ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif($message->status === \App\Enums\MessageStatus::Read)
                                        <svg class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg class="h-4 w-4 -ml-2 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif($message->status === \App\Enums\MessageStatus::Failed)
                                        <svg class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="flex items-center justify-center h-full">
                <p class="text-gray-500 dark:text-gray-400">{{ __('inbox.labels.no_messages_yet') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Message Input Area --}}
    <div class="border-t border-gray-200 dark:border-gray-700 p-3 sm:p-5 bg-white dark:bg-gray-800">
        {{-- Internal Note Toggle --}}
        <div class="mb-4">
            <label class="inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    wire:model.live="isInternalNoteMode"
                    class="sr-only peer"
                >
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('inbox.labels.internal_note') }}</span>
            </label>
        </div>

        {{-- Channel Selector --}}
        @if(!$isInternalNoteMode && $lead->last_message_channel === null && $preferredChannelOverride === null && count($this->availableChannels) > 1)
            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 flex items-center gap-3 flex-wrap">
                <span class="text-sm font-medium text-blue-900 dark:text-blue-200">{{ __('inbox.labels.choose_channel_to_send') }}</span>
                @foreach($this->availableChannels as $ch)
                    <button
                        wire:click="selectChannel('{{ $ch->value }}')"
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 border border-blue-300 dark:border-blue-700 text-sm font-medium rounded-lg text-blue-800 dark:text-blue-200 bg-white dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-800/40 transition-colors"
                    >
                        <x-channel-icon :channel="$ch" class="w-4 h-4 mr-1" />
                        @if($ch === \App\Enums\Channel::Whatsapp)
                            {{ __('inbox.labels.channel_whatsapp') }}
                        @elseif($ch === \App\Enums\Channel::FacebookMessenger)
                            {{ __('inbox.labels.channel_facebook') }}
                        @elseif($ch === \App\Enums\Channel::Instagram)
                            {{ __('inbox.labels.channel_instagram') }}
                        @endif
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Image Upload Preview --}}
        @if($image && !$isInternalNoteMode)
            <div class="mb-4 p-3 sm:p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600">
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="shrink-0 w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ __('inbox.labels.image_ready') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $image->getClientOriginalName() }}</p>
                    </div>
                </div>
                <input
                    type="text"
                    wire:model="imageCaption"
                    placeholder="{{ __('inbox.labels.image_caption') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500 px-3 py-2 text-sm mb-3"
                >
                @error('imageCaption')
                    <p class="text-xs text-red-600 dark:text-red-400 -mt-2 mb-2">{{ $message }}</p>
                @enderror
                @error('image')
                    <p class="text-sm text-red-600 dark:text-red-400 mb-2">{{ $message }}</p>
                @enderror
                <div class="flex gap-2">
                    <button
                        wire:click="sendImage"
                        type="button"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                    >
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        {{ __('inbox.labels.send') }}
                    </button>
                    <button
                        wire:click="$set('image', null)"
                        type="button"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                    >
                        {{ __('inbox.labels.cancel') }}
                    </button>
                </div>
            </div>
        @endif

        {{-- Document Upload Preview --}}
        @if($document && !$isInternalNoteMode)
            <div class="mb-4 p-3 sm:p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600">
                <div class="flex items-center gap-3 mb-3">
                    <div class="shrink-0 w-16 h-16 sm:w-20 sm:h-20 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                        <svg class="h-8 w-8 sm:h-10 sm:w-10 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ __('inbox.labels.document_ready') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $document->getClientOriginalName() }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ number_format($document->getSize() / 1024, 2) }} KB</p>
                    </div>
                </div>
                <input
                    type="text"
                    wire:model="documentCaption"
                    placeholder="{{ __('inbox.labels.document_caption') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500 px-3 py-2 text-sm mb-3"
                >
                @error('documentCaption')
                    <p class="text-xs text-red-600 dark:text-red-400 -mt-2 mb-2">{{ $message }}</p>
                @enderror
                @error('document')
                    <p class="text-sm text-red-600 dark:text-red-400 mb-2">{{ $message }}</p>
                @enderror
                <div class="flex gap-2">
                    <button
                        wire:click="sendDocument"
                        type="button"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                    >
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        {{ __('inbox.labels.send') }}
                    </button>
                    <button
                        wire:click="$set('document', null)"
                        type="button"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                    >
                        {{ __('inbox.labels.cancel') }}
                    </button>
                </div>
            </div>
        @endif

        @if(!$isInternalNoteMode)
            {{-- Regular Message Input --}}
            <form wire:submit="sendMessage" id="message-input">
            @error('newMessage')
                <p class="text-sm text-red-600 dark:text-red-400 mb-2">{{ $message }}</p>
            @enderror

            <div class="flex gap-3">
                {{-- Attach Image Button --}}
                <label class="inline-flex items-center justify-center w-12 h-12 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500 transition-colors cursor-pointer">
                    <input type="file" wire:model="image" accept="image/*" class="sr-only">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </label>

                {{-- Attach Document Button --}}
                <label class="inline-flex items-center justify-center w-12 h-12 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500 transition-colors cursor-pointer">
                    <input type="file" wire:model="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.zip,.rar" class="sr-only">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </label>

                <input
                    type="text"
                    wire:model="newMessage"
                    placeholder="{{ __('inbox.labels.type_message') }}"
                    class="flex-1 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 px-4 py-3"
                >
                <x-filament::button type="submit" icon="heroicon-m-paper-airplane">
                </x-filament::button>
            </div>
        </form>
        @else
            {{-- Internal Note Input --}}
            <form wire:submit="addInternalNote" id="internal-note">
            @error('internalNote')
                <p class="text-sm text-red-600 dark:text-red-400 mb-2">{{ $message }}</p>
            @enderror

            <div class="flex gap-3">
                <textarea
                    wire:model="internalNote"
                    placeholder="{{ __('inbox.labels.internal_note_placeholder') }}"
                    rows="2"
                    class="flex-1 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-yellow-500 focus:ring-yellow-500 px-4 py-3 resize-none"
                ></textarea>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center w-12 h-12 border border-transparent text-sm font-medium rounded-xl text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors self-end"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
            </div>
        </form>
        @endif
    </div>

    {{-- Transfer Modal --}}
    @if($showTransferModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('inbox.labels.transfer_conversation') }}
                </h3>

                <form wire:submit="transferConversation">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('inbox.labels.select_operator') }}
                        </label>
                        <select
                            wire:model="transferToUserId"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">{{ __('inbox.labels.select_operator_placeholder') }}</option>
                            @foreach($this->operators as $operator)
                                <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                            @endforeach
                        </select>
                        @error('transferToUserId')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            wire:click="closeTransferModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            {{ __('inbox.labels.cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700"
                        >
                            {{ __('inbox.labels.transfer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Merge Modal --}}
    @if($showMergeModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('inbox.labels.merge_lead') }}
                </h3>

                <form wire:submit="confirmMerge">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('inbox.labels.select_merge_lead') }}
                        </label>
                        <select
                            wire:model="mergeTargetLeadId"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">-- {{ __('inbox.labels.select_merge_lead') }} --</option>
                            @foreach($this->leadsForMerge as $mergeLead)
                                <option value="{{ $mergeLead->id }}">{{ $mergeLead->full_name }}</option>
                            @endforeach
                        </select>
                        @error('mergeTargetLeadId')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <p class="text-sm text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg px-3 py-2 mb-4">
                        {{ __('inbox.labels.merge_warning') }}
                    </p>

                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            wire:click="closeMergeModal"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            {{ __('inbox.labels.cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700"
                        >
                            {{ __('inbox.labels.confirm_merge') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
