<?php

namespace App\Enums;

enum LeadField: string
{
    case FullName = 'full_name';
    case Email = 'email';
    case Phone = 'phone';
    case Whatsapp = 'whatsapp';
    case StreetName = 'street_name';
    case StreetNumber = 'number';
    case Complement = 'complement';
    case Neighborhood = 'neighborhood';
    case City = 'city';
    case PostalCode = 'postal_code';
    case Country = 'country';
    case Notes = 'notes';

    public function label(): string
    {
        return __('lead_widgets.lead_fields.'.$this->value);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
