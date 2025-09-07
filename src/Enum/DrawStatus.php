<?php

namespace App\Enum;

enum DrawStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case DRAWN = 'drawn';
    case CLOSED = 'closed';
    case EXPIRED = 'expired';
}
