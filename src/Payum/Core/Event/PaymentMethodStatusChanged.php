<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\StoredPaymentMethodInterface;
use Payum\Core\Result\Result;
use Payum\Core\Result\StoredPaymentMethodStatus;

/**
 * A stored payment method moved from one status to another. Redelivering the same details is silent.
 */
final class PaymentMethodStatusChanged extends Event
{
    /**
     * @param CommandInterface<Result> $command
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly StoredPaymentMethodInterface $method,
        public readonly ?StoredPaymentMethodStatus $from,
        public readonly StoredPaymentMethodStatus $to,
    ) {
        parent::__construct($command, $context);
    }
}
