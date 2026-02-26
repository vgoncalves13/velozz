<?php

namespace App\Enums;

enum LeadActivityType: string
{
    case Creation = 'creation';
    case Updated = 'updated';
    case Assigned = 'assigned';
    case StageChanged = 'stage_changed';
    case MessageSent = 'message_sent';
    case MessageReceived = 'message_received';
    case Note = 'note';
    case FieldUpdated = 'field_updated';
    case Imported = 'imported';
    case Transfer = 'transfer';
}
