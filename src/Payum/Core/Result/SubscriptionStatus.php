<?php

declare(strict_types=1);

namespace Payum\Core\Result;

enum SubscriptionStatus: string
{
    case Pending = 'pending';

    case Trialing = 'trialing';

    case Active = 'active';

    case PastDue = 'past_due';

    case Paused = 'paused';

    case Canceled = 'canceled';

    case Expired = 'expired';

    case Unknown = 'unknown';
}
