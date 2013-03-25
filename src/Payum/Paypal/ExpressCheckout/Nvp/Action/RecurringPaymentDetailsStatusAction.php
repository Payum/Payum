<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Action\ActionInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Request\StatusRequestInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class RecurringPaymentDetailsStatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $model = ArrayObject::ensureArrayObject($request->getModel());

        foreach (range(0, 9) as $index) {
            if ($model['L_ERRORCODE'.$index]) {
                $request->markFailed();

                return;
            }
        }

        if (false == $model['PROFILESTATUS'] && false == $model['STATUS']) {
            $request->markNew();

            return;
        }

        if (Api::RECURRINGPAYMENTSTATUS_ACTIVE == $model['STATUS']) {
            $request->markSuccess();

            return;
        }

        if (Api::RECURRINGPAYMENTSTATUS_CANCELLED == $model['STATUS']) {
            $request->markCanceled();

            return;
        }

        if (Api::RECURRINGPAYMENTSTATUS_PENDING == $model['STATUS']) {
            $request->markPending();

            return;
        }

        if (Api::RECURRINGPAYMENTSTATUS_EXPIRED == $model['STATUS']) {
            $request->markExpired();

            return;
        }

        if (Api::RECURRINGPAYMENTSTATUS_SUSPENDED == $model['STATUS']) {
            $request->markSuspended();

            return;
        }

        if (Api::PROFILESTATUS_PENDINGPROFILE == $model['PROFILESTATUS']) {
            $request->markPending();
            
            return;
        }

        if (Api::PROFILESTATUS_ACTIVEPROFILE == $model['PROFILESTATUS']) {
            $request->markSuccess();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof StatusRequestInterface) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return false;
        }

        return isset($model['BILLINGPERIOD']) && null !== $model['BILLINGPERIOD'];
    }
}