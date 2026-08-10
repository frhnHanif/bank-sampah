<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case Pending = 'PENDING';
    case Partial = 'PARTIAL';
    case Settled = 'SETTLED';
}
