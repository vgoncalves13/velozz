<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-start gap-3">
            @foreach ($this->getFormActions() as $action)
                {{ $action }}
            @endforeach

            @foreach ($this->getActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>
