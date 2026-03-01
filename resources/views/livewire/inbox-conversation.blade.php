<div class="flex flex-col h-full"
     wire:poll.3s
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
    <div class="border-b border-gray-200 dark:border-gray-700 p-6">
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
                        {{ $lead->primary_whatsapp ?? $lead->first_whatsapp ?? 'No WhatsApp' }}
                    </p>
                </div>
            </div>

            <div class="flex gap-3">
                @if(!$lead->assigned_user_id || $lead->assigned_user_id !== auth()->id())
                    <button
                        wire:click="assumeConversation"
                        class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Assume
                    </button>
                @endif

                <button
                    wire:click="openTransferModal"
                    class="inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Transfer
                </button>
            </div>
        </div>

        @if($lead->assigned_user_id)
            <div class="mt-4 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Assigned to: <span class="font-medium text-gray-900 dark:text-white">{{ $lead->assignedUser?->name ?? 'Unknown' }}</span></span>
            </div>
        @endif
    </div>

    {{-- Messages Area --}}
    <div
        class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50 dark:bg-gray-900"
        wire:poll.3s
        x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }"
        x-init="scrollToBottom(); $watch('$wire.refreshKey', () => scrollToBottom())"
    >
        @forelse($this->messages as $message)
            @if($message->type === \App\Enums\MessageType::InternalNote)
                {{-- Internal Note --}}
                <div class="flex justify-center">
                    <div class="max-w-2xl px-4 py-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 text-yellow-900 dark:text-yellow-200 rounded-lg text-sm">
                        <p class="font-semibold mb-1">📝 Internal Note</p>
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
                <p class="text-gray-500 dark:text-gray-400">No messages yet</p>
            </div>
        @endforelse
    </div>

    {{-- Message Input Area --}}
    <div class="border-t border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-gray-800">
        {{-- Internal Note Toggle --}}
        <div class="mb-4">
            <label class="inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    wire:model.live="isInternalNoteMode"
                    class="sr-only peer"
                >
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Internal Note</span>
            </label>
        </div>

        {{-- Image Upload Preview --}}
        @if($image && !$isInternalNoteMode)
            <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600">
                <div class="flex items-start gap-4">
                    <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="w-24 h-24 object-cover rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">Image ready to send</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $image->getClientOriginalName() }}</p>
                        <input
                            type="text"
                            wire:model="imageCaption"
                            placeholder="Add a caption (optional)..."
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500 px-3 py-2 text-sm"
                        >
                        @error('imageCaption')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-2">
                        <button
                            wire:click="sendImage"
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                        >
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Send
                        </button>
                        <button
                            wire:click="$set('image', null)"
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
                @error('image')
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
                @enderror
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </label>

                <input
                    type="text"
                    wire:model="newMessage"
                    placeholder="Type a message..."
                    class="flex-1 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 px-4 py-3"
                >
                <button
                    type="submit"
                    class="inline-flex items-center justify-center w-12 h-12 border border-transparent text-sm font-medium rounded-xl text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
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
                    placeholder="Add an internal note (not sent to customer)..."
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
                    Transfer Conversation
                </h3>

                <form wire:submit="transferConversation">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Select Operator
                        </label>
                        <select
                            wire:model="transferToUserId"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">-- Select Operator --</option>
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
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700"
                        >
                            Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
