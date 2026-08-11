<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use Payum\Core\Result\PaymentStatus;

/**
 * Opt in to have Payum keep the subject's status current.
 *
 * A subject that implements this has its status written after every command that concludes something
 * about it, which makes the status readable without asking a gateway and queryable in whatever the model
 * is stored in. One that does not implement it has no status tracked at all.
 *
 * Implemented by whatever a command operates on, so a payout tracks its status the same way a payment
 * does -- reaching PaymentStatus::PaidOut rather than PaymentStatus::Captured.
 *
 * Implementations should start at {@see PaymentStatus::New}.
 */
interface StatusAwareInterface
{
    public function getStatus(): PaymentStatus;

    public function setStatus(PaymentStatus $status): void;
}
