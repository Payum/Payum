<?php

namespace Payum\Offline;

abstract class Constants
{
    public const FIELD_PAID = 'paid';

    public const FIELD_PAYOUT = 'payout';

    public const FIELD_STATUS = 'status';

    public const STATUS_CAPTURED = 'captured';

    public const STATUS_AUTHORIZED = 'authorized';

    public const STATUS_PAYEDOUT = 'payedout';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_PENDING = 'pending';
    
    public const STATUS_CANCELED = 'canceled';

    final private function __construct()
    {
    }
}
