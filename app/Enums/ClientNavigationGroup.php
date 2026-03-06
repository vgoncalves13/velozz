<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ClientNavigationGroup implements HasLabel
{
    case Crm;
    case Sales;
    case WhatsApp;
    case Communication;
    case Catalog;
    case LeadWidgets;
    case Integrations;
    case Configuration;
    case Reports;
    case Management;
    case System;

    public function getLabel(): string
    {
        return match ($this) {
            self::Crm => __('navigation.groups.crm'),
            self::Sales => __('navigation.groups.sales'),
            self::WhatsApp => 'WhatsApp',
            self::Communication => __('navigation.groups.communication'),
            self::Catalog => __('navigation.groups.catalog'),
            self::LeadWidgets => __('navigation.groups.lead_widgets'),
            self::Integrations => __('navigation.groups.integrations'),
            self::Configuration => __('navigation.groups.configuration'),
            self::Reports => __('navigation.groups.reports'),
            self::Management => __('navigation.groups.management'),
            self::System => __('navigation.groups.system'),
        };
    }
}
