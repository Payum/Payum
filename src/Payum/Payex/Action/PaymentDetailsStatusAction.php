<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Api\OrderApi;

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

        //TODO: It may be not correct for all cases. This does NOT indicate wether the transaction requested was successful, only wether the request was carried out successfully.
        if ($model['errorCode'] && OrderApi::ERRORCODE_OK != $model['errorCode']) {
            $request->markFailed();

            return;
        }

        $recurringCanceledStatuses = array(
            RecurringApi::RECURRINGSTATUS_STOPPEDBYADMIN,
            RecurringApi::RECURRINGSTATUS_STOPPEDBYCLIENT,
            RecurringApi::RECURRINGSTATUS_STOPPEDBYMERCHANT,
            RecurringApi::RECURRINGSTATUS_STOPPEDBYSYSTEM,
        );
        if (
            is_numeric($model['recurringStatus']) &&
            in_array($model['recurringStatus'], $recurringCanceledStatuses)
        ) {
            $request->markCanceled();

            return;
        }

        if (
            is_numeric($model['recurringStatus']) &&
            RecurringApi::RECURRINGSTATUS_FAILED == $model['recurringStatus']
        ) {
            $request->markFailed();

            return;
        }

        if (count(iterator_to_array($model)) == 0) {
            $request->markNew();

            return;
        }

        if (null === $model['orderStatus']) {
            $request->markNew();

            return;
        }

        //A purchase has been done, but check the transactionStatus to see the result
        if (OrderApi::ORDERSTATUS_COMPLETED == $model['orderStatus']) {
            if (OrderApi::TRANSACTIONSTATUS_CANCEL == $model['transactionStatus']) {
                $request->markCanceled();

                return;
            }

            if (OrderApi::TRANSACTIONSTATUS_FAILURE == $model['transactionStatus']) {
                $errorDetails = $model['errorDetails'];
                if (
                    isset($errorDetails['transactionErrorCode']) &&
                    $errorDetails['transactionErrorCode'] == OrderApi::TRANSACTIONERRORCODE_OPERATIONCANCELLEDBYCUSTOMER
                ) {
                    $request->markCanceled();

                    return;
                }

                $request->markFailed();

                return;
            }

            //If you are running 2-phase transactions, you should check that the node transactionStatus contains 3 (authorize)
            if (OrderApi::PURCHASEOPERATION_AUTHORIZATION == $model['purchaseOperation']) {
                if (OrderApi::TRANSACTIONSTATUS_AUTHORIZE == $model['transactionStatus']) {
                    $request->markCaptured();

                    return;
                }

                //Anything else indicates that the transaction has failed or is still processing
                $request->markFailed();

                return;
            }

            //If you are running 1-phase transactions, you should check that the node transactionStatus contains 0 (sale)
            if (OrderApi::PURCHASEOPERATION_SALE == $model['purchaseOperation']) {
                if (is_numeric($model['transactionStatus']) && OrderApi::TRANSACTIONSTATUS_SALE == $model['transactionStatus']) {
                    $request->markCaptured();

                    return;
                }

                //Anything else indicates that the transaction has failed or is still processing
                $request->markFailed();

                return;
            }

            $request->markUnknown();

            return;
        }

        if (OrderApi::ORDERSTATUS_PROCESSING == $model['orderStatus']) {
            $request->markPending();

            return;
        }

        //PxOrder.Complete can return orderStatus 1 for 2 weeks after PxOrder.Initialize is called. Afterwards the orderStatus will be set to 2
        if (OrderApi::ORDERSTATUS_NOT_FOUND == $model['orderStatus']) {
            $request->markExpired();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == (
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        )) {
            return false;
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (count(iterator_to_array($model)) == 0) {
            return true;
        }

        if ($model['recurring']) {
            return true;
        }

        //Make sure it is not auto pay payment. There is an other capture action for auto pay payments;
        if (isset($model['autoPay']) && false == $model['autoPay']) {
            return true;
        }

        return false;
    }
}
