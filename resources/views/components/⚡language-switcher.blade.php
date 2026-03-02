<?php

use Livewire\Component;

new class extends Component
{
    public string $currentLocale;

    public function mount(): void
    {
        $this->currentLocale = auth()->user()->locale ?? 'en';
    }

    public function switchLocale(string $locale): void
    {
        if (! in_array($locale, ['en', 'pt'])) {
            return;
        }

        auth()->user()->update(['locale' => $locale]);
        $this->currentLocale = $locale;

        // Refresh the page to apply new locale
        $this->redirect(request()->header('Referer'));
    }
};
?>

<div class="flex items-center gap-2">
    <button
        wire:click="switchLocale('en')"
        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors
            {{ $currentLocale === 'en' ? 'bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
        type="button"
    >
        <span>🇬🇧</span>
        <span>English</span>
    </button>

    <button
        wire:click="switchLocale('pt')"
        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors
            {{ $currentLocale === 'pt' ? 'bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
        type="button"
    >
        <span>🇵🇹</span>
        <span>Português</span>
    </button>
</div>