<x-filament-panels::page>
    {{-- Actual Content --}}
    <div
        wire:loading.remove
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4"
        x-init="
            // Enable drop zones on cards containers
            $nextTick(() => {
                document.querySelectorAll('.space-y-3').forEach(container => {
                    container.addEventListener('drop', (e) => {
                        e.preventDefault();
                        const leadId = e.dataTransfer.getData('leadId');
                        const stageId = container.closest('[data-stage-id]')?.dataset.stageId;
                        if (leadId && stageId) {
                            @this.moveCard(parseInt(leadId), parseInt(stageId));
                        }
                    });
                });
            });
        "
    >
        @forelse($stages as $stage)
            <div
                class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 md:p-4 transition-all"
                data-stage-id="{{ $stage->id }}"
                x-data="{
                    collapsed: JSON.parse(localStorage.getItem('kanban_stage_{{ $stage->id }}_collapsed') || 'false'),
                    toggle() {
                        this.collapsed = !this.collapsed;
                        localStorage.setItem('kanban_stage_{{ $stage->id }}_collapsed', this.collapsed);
                    }
                }"
            >
                    {{-- Stage Header --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2 flex-1">
                                <button
                                    @click="toggle()"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
                                >
                                    <x-filament::icon
                                        icon="heroicon-o-chevron-down"
                                        class="w-4 h-4 transition-transform"
                                        ::class="{ 'rotate-180': collapsed }"
                                    />
                                </button>
                                <h3 class="font-bold text-lg" style="color: {{ $stage->color }}">
                                    <x-filament::icon
                                        :icon="$stage->icon ?? 'heroicon-o-queue-list'"
                                        class="w-5 h-5 inline"
                                    />
                                    {{ $stage->name }}
                                </h3>
                            </div>
                            <span class="text-sm font-semibold px-2 py-1 rounded-full bg-gray-200 dark:bg-gray-700">
                                {{ count($records[$stage->id] ?? []) }}
                            </span>
                        </div>
                        @if($stage->sla_hours)
                            <p class="text-xs text-gray-500 dark:text-gray-400 ml-6" x-show="!collapsed">
                                {{ __('kanban.labels.sla') }}: {{ $stage->sla_hours }}h
                            </p>
                        @endif
                    </div>

                    {{-- Cards Container --}}
                    <div
                        class="space-y-3 min-h-[200px] transition-all rounded-lg"
                        x-show="!collapsed"
                        x-collapse
                        @dragover.prevent="$el.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20')"
                        @dragleave="$el.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20')"
                        @drop="$el.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20')"
                    >
                        @forelse($records[$stage->id] ?? [] as $lead)
                            <div
                                class="bg-white dark:bg-gray-900 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600 transition-all cursor-move"
                                draggable="true"
                                @dragstart="
                                    $event.dataTransfer.setData('leadId', {{ $lead['id'] }});
                                    $event.target.style.opacity = '0.5';
                                "
                                @dragend="$event.target.style.opacity = '1'"
                            >
                                {{-- Lead Info --}}
                                <div class="mb-2">
                                    <h4 class="font-semibold text-sm mb-1">
                                        {{ $lead['full_name'] }}
                                    </h4>
                                    @if($lead['email'])
                                        <div class="flex items-center gap-1 group" x-data="{ copied: false }">
                                            <p class="text-xs text-gray-600 dark:text-gray-400 truncate min-w-0">
                                                {{ $lead['email'] }}
                                            </p>
                                            <button
                                                type="button"
                                                @mousedown.stop
                                                x-on:click.stop="
                                                    navigator.clipboard.writeText({{ \Illuminate\Support\Js::from($lead['email']) }});
                                                    copied = true;
                                                    setTimeout(() => copied = false, 2000);
                                                "
                                                :title="copied ? '{{ __('kanban.labels.email_copied') }}' : '{{ __('kanban.labels.copy_email') }}'"
                                                class="shrink-0 text-gray-300 group-hover:text-gray-500 dark:text-gray-600 dark:group-hover:text-gray-400 transition-colors"
                                            >
                                                <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <svg x-show="copied" x-cloak class="w-3.5 h-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                {{-- Priority Badge --}}
                                @if($lead['priority'])
                                    <div class="mb-2">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md
                                            @if($lead['priority'] === 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @elseif($lead['priority'] === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                            @elseif($lead['priority'] === 'medium') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                            @endif
                                        ">
                                            {{ ucfirst($lead['priority']) }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Assigned User --}}
                                @if(!empty($lead['assigned_user']))
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <x-filament::icon icon="heroicon-o-user" class="w-3 h-3 inline" />
                                        {{ $lead['assigned_user']['name'] ?? 'N/A' }}
                                    </p>
                                @endif

                                {{-- Actions --}}
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2">
                                    <a
                                        href="{{ \App\Filament\Client\Resources\Leads\LeadResource::getUrl('view', ['record' => $lead['id']]) }}"
                                        class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                        @mousedown.stop
                                    >
                                        {{ __('kanban.actions.view') }}
                                    </a>
                                    <a
                                        href="{{ \App\Filament\Client\Resources\Leads\LeadResource::getUrl('edit', ['record' => $lead['id']]) }}"
                                        class="text-xs text-gray-600 hover:text-gray-800 dark:text-gray-400"
                                        @mousedown.stop
                                    >
                                        {{ __('kanban.actions.edit') }}
                                    </a>
                                    <div class="ml-auto" @mousedown.stop>
                                        <select
                                            title="{{ __('kanban.labels.move_to_stage') }}"
                                            @mousedown.stop
                                            @change.stop="$wire.moveCard({{ $lead['id'] }}, parseInt($event.target.value))"
                                            class="text-xs rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 py-0.5 pl-1.5 pr-6 cursor-pointer focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                        >
                                            @foreach($stages as $s)
                                                <option value="{{ $s->id }}" {{ $lead['pipeline_stage_id'] == $s->id ? 'selected' : '' }}>
                                                    {{ $s->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="bg-gray-100 dark:bg-gray-700 rounded-lg p-6 text-center text-gray-500 dark:text-gray-400"
                                @drop.prevent="
                                    const leadId = $event.dataTransfer.getData('leadId');
                                    if (leadId) $wire.moveCard(parseInt(leadId), {{ $stage->id }});
                                "
                                @dragover.prevent
                            >
                                <x-filament::icon
                                    :icon="$stage->icon ?? 'heroicon-o-queue-list'"
                                    class="w-8 h-8 mx-auto mb-2 opacity-30"
                                />
                                <p class="text-xs">{{ __('kanban.labels.drop_here') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @empty
            <div class="col-span-full flex items-center justify-center py-16">
                <div class="text-center max-w-md">
                    <svg class="mx-auto h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">{{ __('kanban.empty.no_stages_title') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('kanban.empty.no_stages_description') }}</p>
                    <a href="/app/pipeline-stages/create" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('kanban.actions.create_stage') }}
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
