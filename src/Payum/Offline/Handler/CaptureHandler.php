<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Offline\Constants;
use Payum\Offline\OfflineConfig;
use Payum\Offline\OfflineDetails;

final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly OfflineConfig $config,
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        if (null !== $command->amount) {
            throw new LogicException('The Offline gateway captures a payment in full. Replace its capture handler to take part of one.');
        }

        $state = $context->state();

        if ($context->payment() instanceof PaymentInterface) {
            OfflineDetails::fillFromPayment($context->payment(), $state);
        }

        $state->defaults([
            Constants::FIELD_PAID => $this->config->paid,
        ]);

        return $state[Constants::FIELD_PAID] ? CaptureResult::captured() : CaptureResult::pending();
    }
}
