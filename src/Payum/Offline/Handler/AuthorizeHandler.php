<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Handler\AuthorizeHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Result\AuthorizeResult;
use Payum\Offline\Constants;
use Payum\Offline\OfflineConfig;
use Payum\Offline\OfflineDetails;

final class AuthorizeHandler implements AuthorizeHandlerInterface
{
    public function __construct(
        private readonly OfflineConfig $config,
    ) {
    }

    public function handle(AuthorizeCommand $command, Context $context): AuthorizeResult
    {
        if (null !== $command->amount) {
            throw new LogicException('The Offline gateway authorizes a payment in full. Replace its authorize handler to hold part of one.');
        }

        $state = $context->state();

        if ($context->payment() instanceof PaymentInterface) {
            OfflineDetails::fillFromPayment($context->payment(), $state);
        }

        $state->defaults([
            Constants::FIELD_PAID => $this->config->paid,
        ]);

        return $state[Constants::FIELD_PAID] ? AuthorizeResult::authorized() : AuthorizeResult::pending();
    }
}
