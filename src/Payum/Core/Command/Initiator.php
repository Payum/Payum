<?php

declare(strict_types=1);

namespace Payum\Core\Command;

enum Initiator: string
{
    case Customer = 'customer';

    case Merchant = 'merchant';
}
