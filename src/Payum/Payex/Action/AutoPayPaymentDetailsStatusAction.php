<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Api\AgreementApi;
use Payum\Core\Request\GetStatusInterface;
use Payum\Payex\Api\OrderApi;

class AutoPayPaymentDetailsStatusAction implements ActionInterface
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

        if (null === $model['transactionStatus']) {
            $request->markNew();

            return;
        }

        if (
            AgreementApi::PURCHASEOPERATION_SALE == $model['purchaseOperation'] &&
            OrderApi::TRANSACTIONSTATUS_SALE == $model['transactionStatus']
        ) {
            $request->markCaptured();

            return;
        }

        if (
            AgreementApi::PURCHASEOPERATION_AUTHORIZATION == $model['purchaseOperation'] &&
            OrderApi::TRANSACTIONSTATUS_AUTHORIZE == $model['transactionStatus']
        ) {
            $request->markCaptured();

            return;
        }

        $request->markFailed();
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

        //Make sure it is not recurring payment. There is an status action for recurring payments;
        if (true == $model['recurring']) {
            return false;
        }

        if ($model['autoPay']) {
            return true;
        }

        return false;
    }
}
