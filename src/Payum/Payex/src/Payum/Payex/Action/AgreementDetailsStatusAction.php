<?php
namespace Payum\Payex\Action;

use Payum\Action\ActionInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Payex\Api\AgreementApi;
use Payum\Request\StatusRequestInterface;
use Payum\Payex\Api\OrderApi;

class AgreementDetailsStatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

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
            $request->markSuccess();

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
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof \ArrayAccess &&
            //Make sure it is payment. Apparently an order(payment) does not have this field.
            $request->getModel()->offsetExists('agreementRef') &&
            false == $request->getModel()->offsetExists('orderId')
        ;
    }
}