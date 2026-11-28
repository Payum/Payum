<?php

declare(strict_types=1);

namespace Payum\Core\Result;

enum StoredPaymentMethodStatus: string
{
    case Pending = 'pending';

    case Active = 'active';

    case Revoked = 'revoked';

    case Expired = 'expired';

    case Failed = 'failed';
}
