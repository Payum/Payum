<?php
namespace Payum\Paypal\ProHosted\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Paypal\ProHosted\Nvp\Api;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        foreach (range(0, 9) as $index) {
            if ($model['L_ERRORCODE'.$index]) {
                $request->markFailed();

                return;
            }
        }

        if (isset($model['CANCELLED'])) {
            $request->markCanceled();

            return;
        }

        if (null === $paymentStatus = $model['PAYMENTSTATUS']) {
            $request->markUnknown();

            return;
        }

        $refundStatuses = [
            Api::PAYMENTSTATUS_REFUNDED,
            Api::PAYMENTSTATUS_PARTIALLY_REFUNDED,
        ];

        if (in_array($paymentStatus, $refundStatuses)) {
            $request->markRefunded();

            return;
        }

        if ($paymentStatus == Api::PAYMENTSTATUS_COMPLETED) {
            $request->markCaptured();

            return;
        }

        $pendingStatuses = [
            Api::PAYMENTSTATUS_IN_PROGRESS,
            Api::PAYMENTSTATUS_PENDING,
        ];

        if (in_array($paymentStatus, $pendingStatuses)) {
            if (Api::PENDINGREASON_AUTHORIZATION == $model['PENDINGREASON']) {
                $request->markAuthorized();

                return;
            }
        }

        if ($paymentStatus == Api::PAYMENTSTATUS_PENDING) {
            $request->markPending();

            return;
        }

        $failedStatuses = array(
            Api::PAYMENTSTATUS_FAILED,
            Api::PAYMENTSTATUS_EXPIRED,
            Api::PAYMENTSTATUS_DENIED,
            Api::PAYMENTSTATUS_CANCELED_REVERSAL,
        );

        if (in_array($paymentStatus, $failedStatuses)) {
            $request->markFailed();

            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
