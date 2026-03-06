<x-filament-panels::page>
    <div
        wire:loading.remove
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4"
        x-init="
            $nextTick(() => {
                document.querySelectorAll('.opp-cards-container').forEach(container => {
                    container.addEventListener('drop', (e) => {
                        e.preventDefault();
                        const opportunityId = e.dataTransfer.getData('opportunityId');
                        const stageId = container.closest('[data-stage-id]')?.dataset.stageId;
                        if (opportunityId && stageId) {
                            @this.moveCard(parseInt(opportunityId), parseInt(stageId));
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
                    collapsed: JSON.parse(localStorage.getItem('opp_kanban_stage_{{ $stage->id }}_collapsed') || 'false'),
                    toggle() {
                        this.collapsed = !this.collapsed;
                        localStorage.setItem('opp_kanban_stage_{{ $stage->id }}_collapsed', this.collapsed);
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
                                    :icon="$stage->icon ?? 'heroicon-o-currency-dollar'"
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
                            {{ __('opportunity_kanban.labels.sla') }}: {{ $stage->sla_hours }}h
                        </p>
                    @endif
                </div>

                {{-- Cards Container --}}
                <div
                    class="opp-cards-container space-y-3 min-h-[200px] transition-all rounded-lg"
                    x-show="!collapsed"
                    x-collapse
                    @dragover.prevent="$el.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20')"
                    @dragleave="$el.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20')"
                    @drop="$el.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20')"
                >
                    @forelse($records[$stage->id] ?? [] as $opportunity)
                        <div
                            class="bg-white dark:bg-gray-900 rounded-lg p-3 shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600 transition-all cursor-move"
                            draggable="true"
                            @dragstart="
                                $event.dataTransfer.setData('opportunityId', {{ $opportunity['id'] }});
                                $event.target.style.opacity = '0.5';
                            "
                            @dragend="$event.target.style.opacity = '1'"
                        >
                            {{-- Lead Name --}}
                            <div class="mb-2">
                                <h4 class="font-semibold text-sm mb-1">
                                    {{ $opportunity['lead']['full_name'] ?? '—' }}
                                </h4>
                                @if(!empty($opportunity['product']['name']))
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <x-filament::icon icon="heroicon-o-cube" class="w-3 h-3 inline" />
                                        {{ $opportunity['product']['name'] }}
                                    </p>
                                @endif
                            </div>

                            {{-- Value & Probability --}}
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-sm font-bold text-green-600 dark:text-green-400">
                                    € {{ number_format($opportunity['value'], 2, ',', '.') }}
                                </span>
                                @if(isset($opportunity['probability']))
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-auto">
                                        {{ $opportunity['probability'] }}%
                                    </span>
                                @endif
                            </div>

                            {{-- Assigned User --}}
                            @if(!empty($opportunity['assigned_user']))
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    <x-filament::icon icon="heroicon-o-user" class="w-3 h-3 inline" />
                                    {{ $opportunity['assigned_user']['name'] ?? 'N/A' }}
                                </p>
                            @endif

                            {{-- Actions --}}
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2">
                                <a
                                    href="{{ \App\Filament\Client\Resources\Opportunities\OpportunityResource::getUrl('edit', ['record' => $opportunity['id']]) }}"
                                    class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                    @mousedown.stop
                                >
                                    {{ __('opportunity_kanban.actions.edit') }}
                                </a>
                                <div class="ml-auto" @mousedown.stop>
                                    <select
                                        title="{{ __('opportunity_kanban.labels.move_to_stage') }}"
                                        @mousedown.stop
                                        @change.stop="$wire.moveCard({{ $opportunity['id'] }}, parseInt($event.target.value))"
                                        class="text-xs rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 py-0.5 pl-1.5 pr-6 cursor-pointer focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                    >
                                        @foreach($stages as $s)
                                            <option value="{{ $s->id }}" {{ $opportunity['opportunity_stage_id'] == $s->id ? 'selected' : '' }}>
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
                                const opportunityId = $event.dataTransfer.getData('opportunityId');
                                if (opportunityId) $wire.moveCard(parseInt(opportunityId), {{ $stage->id }});
                            "
                            @dragover.prevent
                        >
                            <x-filament::icon
                                :icon="$stage->icon ?? 'heroicon-o-currency-dollar'"
                                class="w-8 h-8 mx-auto mb-2 opacity-30"
                            />
                            <p class="text-xs">{{ __('opportunity_kanban.labels.drop_here') }}</p>
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
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-white">{{ __('opportunity_kanban.empty.no_stages_title') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('opportunity_kanban.empty.no_stages_description') }}</p>
                    <a href="/app/opportunity-stages/create" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('opportunity_kanban.actions.create_stage') }}
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
