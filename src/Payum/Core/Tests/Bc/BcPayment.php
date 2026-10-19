<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use Payum\Core\Model\Payment;
use function array_keys;
use function get_object_vars;

/**
 * Counts how often it is written to storage, which is how a second write in one execution shows up.
 */
class BcPayment extends Payment
{
    public static int $writes = 0;

    /**
     * @return list<string>
     */
    public function __sleep(): array
    {
        ++self::$writes;

        return array_keys(get_object_vars($this));
    }
}
