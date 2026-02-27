<x-filament-widgets::widget>
    <x-filament::section collapsible collapsed>
        <x-slot name="heading">
            Activity Timeline
        </x-slot>

        <x-slot name="description">
            Track all changes and interactions with this lead
        </x-slot>

        <div class="space-y-0">
            @forelse($this->getActivities() as $activity)
                <div class="relative flex gap-4 pb-8 @if($loop->last) pb-0 @endif">
                    {{-- Vertical line (only if not last) --}}
                    @if(!$loop->last)
                        <div class="absolute left-5 top-10 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                    @endif

                    {{-- Icon/Badge --}}
                    <div class="relative z-10 flex-shrink-0">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full
                            @if($activity->type->value === 'creation') bg-green-100 dark:bg-green-900
                            @elseif($activity->type->value === 'assigned') bg-blue-100 dark:bg-blue-900
                            @elseif($activity->type->value === 'stage_changed') bg-purple-100 dark:bg-purple-900
                            @elseif($activity->type->value === 'message_sent') bg-indigo-100 dark:bg-indigo-900
                            @elseif($activity->type->value === 'message_received') bg-cyan-100 dark:bg-cyan-900
                            @else bg-gray-100 dark:bg-gray-800
                            @endif
                        ">
                            @if($activity->type->value === 'creation')
                                <x-heroicon-o-plus class="h-5 w-5 text-green-600 dark:text-green-400"/>
                            @elseif($activity->type->value === 'assigned')
                                <x-heroicon-o-user class="h-5 w-5 text-blue-600 dark:text-blue-400"/>
                            @elseif($activity->type->value === 'stage_changed')
                                <x-heroicon-o-arrow-right class="h-5 w-5 text-purple-600 dark:text-purple-400"/>
                            @elseif($activity->type->value === 'message_sent')
                                <x-heroicon-o-paper-airplane class="h-5 w-5 text-indigo-600 dark:text-indigo-400"/>
                            @elseif($activity->type->value === 'message_received')
                                <x-heroicon-o-inbox class="h-5 w-5 text-cyan-600 dark:text-cyan-400"/>
                            @else
                                <x-heroicon-o-pencil class="h-5 w-5 text-gray-600 dark:text-gray-400"/>
                            @endif
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 pt-1">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $activity->description }}
                                </p>

                                @if($activity->metadata)
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        @foreach($activity->metadata as $key => $value)
                                            @if(!in_array($key, ['lead_id', 'message_id']))
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                    <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                </span>
                                                @if(!$loop->last) • @endif
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    @if($activity->user)
                                        <span class="font-medium">{{ $activity->user->name }}</span>
                                        <span>•</span>
                                    @endif
                                    <time datetime="{{ $activity->created_at->toIso8601String() }}"
                                          title="{{ $activity->created_at->format('M d, Y H:i:s') }}">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </time>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <x-heroicon-o-clock class="mx-auto h-12 w-12 text-gray-400"/>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        No activities yet
                    </p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
