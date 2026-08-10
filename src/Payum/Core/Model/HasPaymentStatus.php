<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use Payum\Core\Result\PaymentStatus;

/**
 * Opt in to have Payum keep the payment's status current.
 *
 * A payment that implements this has its status written after every command that concludes something
 * about it, which makes the status readable without asking a gateway and queryable in whatever the model
 * is stored in. A payment that does not implement this has no status tracked at all.
 *
 * Implementations should start at {@see PaymentStatus::New}.
 */
interface HasPaymentStatus
{
    public function getStatus(): PaymentStatus;

    public function setStatus(PaymentStatus $status): void;
}
