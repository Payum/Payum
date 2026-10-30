<?php

declare(strict_types=1);

namespace Payum\Core\Security;

enum TokenPurpose: string
{
    case Authorize = 'authorize';
    case Capture = 'capture';
    case Refund = 'refund';
    case Payout = 'payout';
    case Notify = 'notify';
}
