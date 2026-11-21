<?php

declare(strict_types=1);

namespace Payum\Core\Command;

enum ChargeReason: string
{
    case Recurring = 'recurring';

    case Unscheduled = 'unscheduled';

    case Installment = 'installment';
}
