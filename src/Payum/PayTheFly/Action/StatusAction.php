<?php

namespace Payum\PayTheFly\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\PayTheFly\Constants;

/**
 * Resolves the payment status based on model data.
 *
 * Maps PayTheFly internal states to Payum's standard status interface.
 */
class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // Check for error state
        if (isset($model['error']) && $model['error']) {
            $request->markFailed();

            return;
        }

        $status = $model['status'] ?? null;

        // No status set yet — this is a new payment
        if (! $status || $status === Constants::STATUS_NEW) {
            $request->markNew();

            return;
        }

        // Payment has been submitted but not confirmed
        if ($status === Constants::STATUS_PENDING) {
            $request->markPending();

            return;
        }

        // Payment confirmed on-chain via webhook
        if ($status === Constants::STATUS_CONFIRMED) {
            $txType = $model['tx_type'] ?? null;

            if ($txType === Constants::TX_TYPE_WITHDRAWAL) {
                // Withdrawal confirmed → payout
                $request->markPayedout();

                return;
            }

            // Payment confirmed → captured
            $request->markCaptured();

            return;
        }

        // Explicitly failed
        if ($status === Constants::STATUS_FAILED) {
            $request->markFailed();

            return;
        }

        // Could not determine status
        $request->markUnknown();
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface
            && $request->getModel() instanceof ArrayAccess;
    }
}
