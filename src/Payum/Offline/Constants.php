<?php
namespace Payum\Offline;

abstract class Constants
{
    const FIELD_PAID = 'paid';

    const FIELD_STATUS = 'status';

    /**
     * @deprecated since 0.12, will be removed. use self::STATUS_CAPTURED instead.
     */
    const STATUS_SUCCESS = 'success';

    const STATUS_CAPTURED = 'captured';

    const STATUS_PENDING = 'pending';

    private final function __construct() {}
}