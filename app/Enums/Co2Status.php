<?php

namespace App\Enums;

enum Co2Status: string
{
    case Pending = 'PENDING';
    case Realized = 'REALIZED';
    case NotApplicable = 'NOT_APPLICABLE';
}
