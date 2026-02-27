<?php

namespace App\Filament\Client\Resources\Leads\Widgets;

use App\Models\Lead;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class LeadActivitiesWidget extends Widget
{
    protected string $view = 'filament.client.resources.leads.widgets.lead-activities-widget';

    public ?Model $record = null;

    public function getActivities()
    {
        if (! $this->record instanceof Lead) {
            return collect();
        }

        return $this->record->activities()
            ->with('user')
            ->latest()
            ->limit(50)
            ->get();
    }
}
