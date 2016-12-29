<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class PaymentDetailsStatusAction implements ActionInterface
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
            if (Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED == $model['L_ERRORCODE'.$index]) {
                $request->markCanceled();

                return;
            }
        }

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

        if (
            false == $model['PAYERID'] &&
            Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $model['CHECKOUTSTATUS']
        ) {
            $request->markPending();

            return;
        }

        //it is possible to set zero amount for create agreement request.
        if (
            $model['PAYERID'] &&
            Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $model['CHECKOUTSTATUS'] &&
            $model['L_BILLINGTYPE0'] &&
            $model['PAYMENTREQUEST_0_AMT'] == 0
        ) {
            $request->markCaptured();

            return;
        }

        if (
            false == $model['CHECKOUTSTATUS'] ||
            Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $model['CHECKOUTSTATUS']
        ) {
            $request->markNew();

            return;
        }

        if (Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS == $model['CHECKOUTSTATUS']) {
            $request->markPending();

            return;
        }
        if (Api::CHECKOUTSTATUS_PAYMENT_ACTION_FAILED == $model['CHECKOUTSTATUS']) {
            $request->markFailed();

            return;
        }

        //todo check all payment statuses.
        if (
            Api::CHECKOUTSTATUS_PAYMENT_COMPLETED == $model['CHECKOUTSTATUS'] ||
            Api::CHECKOUTSTATUS_PAYMENT_ACTION_COMPLETED == $model['CHECKOUTSTATUS']
        ) {
            $completedCounter = 0;
            $authorizedCounter = 0;
            $voidedCounter = 0;
            $allCounter = 0;
            foreach (range(0, 9) as $index) {
                if (null === $paymentStatus = $model['PAYMENTREQUEST_'.$index.'_PAYMENTSTATUS']) {
                    continue;
                }

                $allCounter++;

                $refundStatuses = array(
                    Api::PAYMENTSTATUS_REFUNDED,
                    Api::PAYMENTSTATUS_PARTIALLY_REFUNDED,
                );
                if (in_array($paymentStatus, $refundStatuses)) {
                    $request->markRefunded();

                    return;
                }

                $pendingStatuses = array(
                    Api::PAYMENTSTATUS_IN_PROGRESS,
                    Api::PAYMENTSTATUS_PENDING,
                );
                if (in_array($paymentStatus, $pendingStatuses)) {
                    if (Api::PENDINGREASON_AUTHORIZATION == $model['PAYMENTINFO_'.$index.'_PENDINGREASON']) {
                        $authorizedCounter++;
                    } else {
                        $request->markPending();

                        return;
                    }
                }

                $canceledStatuses = array(
                    Api::PAYMENTSTATUS_VOIDED,
                );
                if (in_array($paymentStatus, $canceledStatuses)) {
                    if (Api::PENDINGREASON_AUTHORIZATION == $model['PAYMENTINFO_'.$index.'_PENDINGREASON']) {
                        $voidedCounter++;
                    }
                }

                $failedStatuses = array(
                    Api::PAYMENTSTATUS_FAILED,
                    Api::PAYMENTSTATUS_EXPIRED,
                    Api::PAYMENTSTATUS_DENIED,
                    Api::PAYMENTSTATUS_REVERSED,
                    Api::PAYMENTSTATUS_CANCELED_REVERSAL,
                );
                if (in_array($paymentStatus, $failedStatuses)) {
                    $request->markFailed();

                    return;
                }

                $completedStatuses = array(
                    Api::PAYMENTSTATUS_COMPLETED,
                    Api::PAYMENTSTATUS_PROCESSED,
                );
                if (in_array($paymentStatus, $completedStatuses)) {
                    $completedCounter++;
                }
            }

            if ($completedCounter === $allCounter) {
                $request->markCaptured();

                return;
            }

            if ($authorizedCounter === $allCounter) {
                $request->markAuthorized();

                return;
            }

            if ($voidedCounter === $allCounter) {
                $request->markCanceled();

                return;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof GetStatusInterface) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return false;
        }

        return false == isset($model['BILLINGPERIOD']);
    }
}
