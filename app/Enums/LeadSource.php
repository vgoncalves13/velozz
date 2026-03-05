<?php

namespace App\Enums;

enum LeadSource: string
{
    case Import = 'import';
    case Manual = 'manual';
    case Api = 'api';
    case Form = 'form';
    case Whatsapp = 'whatsapp';
    case Instagram = 'instagram';
    case FacebookMessenger = 'facebook_messenger';
    case EmbeddedForm = 'embedded_form';
    case WhatsappWidget = 'whatsapp_widget';
}
