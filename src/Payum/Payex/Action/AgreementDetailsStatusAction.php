<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Api\AgreementApi;
use Payum\Core\Request\GetStatusInterface;
use Payum\Payex\Api\OrderApi;

class AgreementDetailsStatusAction implements ActionInterface
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

        if (
            is_numeric($model['agreementStatus']) &&
            AgreementApi::AGREEMENTSTATUS_NOTVERIFIED == $model['agreementStatus']
        ) {
            $request->markNew();

            return;
        }

        if (
            is_numeric($model['agreementStatus']) &&
            AgreementApi::AGREEMENTSTATUS_VERIFIED == $model['agreementStatus']
        ) {
            $request->markCaptured();

            return;
        }

        if (
            is_numeric($model['agreementStatus']) &&
            AgreementApi::AGREEMENTSTATUS_DELETED == $model['agreementStatus']
        ) {
            $request->markCanceled();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess &&
            //Make sure it is payment. Apparently an order(payment) does not have this field.
            $request->getModel()->offsetExists('agreementRef') &&
            false == $request->getModel()->offsetExists('orderId')
        ;
    }
}
