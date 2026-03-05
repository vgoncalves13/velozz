<?php

namespace App\Filament\Client\Resources\LeadWidgets\Pages;

use App\Filament\Client\Resources\LeadWidgets\EmbeddedFormResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditEmbeddedForm extends EditRecord
{
    protected static string $resource = EmbeddedFormResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['_embed_script'] = '<script src="'.url('/embed/form-'.$record->id.'.js').'" data-form="'.$record->id.'"></script><div data-form="'.$record->id.'"></div>';
        $data['_embed_iframe'] = '<iframe src="'.route('forms.show', $record->slug).'" width="100%" frameborder="0" style="border:none;"></iframe>';

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_preview')
                ->label(__('lead_widgets.actions.open_preview'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn () => route('forms.preview', $this->getRecord()->slug))
                ->openUrlInNewTab()
                ->color('gray'),

            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('lead_widgets.embedded_forms.notifications.deleted'))
                ),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('lead_widgets.embedded_forms.notifications.updated'));
    }
}
