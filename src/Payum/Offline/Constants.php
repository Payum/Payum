<?php
namespace Payum\Offline;

abstract class Constants
{
    const FIELD_PAID = 'paid';

    const FIELD_STATUS = 'status';

    const STATUS_CAPTURED = 'captured';

    const STATUS_AUTHORIZED = 'authorized';

    const STATUS_PENDING = 'pending';
    
    const STATUS_CANCELED = 'canceled';

    final private function __construct()
    {
    }
}
