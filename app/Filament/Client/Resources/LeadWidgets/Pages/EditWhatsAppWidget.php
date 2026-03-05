<?php

namespace App\Filament\Client\Resources\LeadWidgets\Pages;

use App\Filament\Client\Resources\LeadWidgets\WhatsAppWidgetResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditWhatsAppWidget extends EditRecord
{
    protected static string $resource = WhatsAppWidgetResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['_embed_script'] = '<script src="'.url('/embed/whatsapp-'.$record->id.'.js').'"></script>';

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_preview')
                ->label(__('lead_widgets.actions.open_preview'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn () => route('whatsapp-widget.preview', $this->getRecord()->id))
                ->openUrlInNewTab()
                ->color('gray'),

            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('lead_widgets.whatsapp_widgets.notifications.deleted'))
                ),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('lead_widgets.whatsapp_widgets.notifications.updated'));
    }
}
