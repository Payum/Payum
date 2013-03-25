<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Action\ActionInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Request\StatusRequestInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class PaymentDetailsStatusAction implements ActionInterface
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
            if (Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED === $model['L_ERRORCODE'.$index]) {
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
        
        //treat this situation as canceled. In other case we can get into an endless cycle.
        if (
            false == $model['PAYERID'] &&
            Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $model['CHECKOUTSTATUS'] 
        ) {
            $request->markCanceled();

            return;
        }
        
        //it is possible to set zero amount for create agreement request.
        if (
            $model['PAYERID'] && 
            Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $model['CHECKOUTSTATUS'] &&
            $model['L_BILLINGTYPE0'] && 
            $model['PAYMENTREQUEST_0_AMT'] == 0
        ) {
            $request->markSuccess();

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
            $successCounter = 0;
            $allCounter = 0;
            foreach (range(0, 9) as $index) {
                if (null === $paymentStatus = $model['PAYMENTREQUEST_'.$index.'_PAYMENTSTATUS']) {
                    continue;
                }

                $allCounter++;
                
                $inProgress = array(
                    Api::PAYMENTSTATUS_IN_PROGRESS,
                    Api::PAYMENTSTATUS_PENDING,
                );
                if (in_array($paymentStatus, $inProgress)) {
                    $request->markPending();

                    return;
                }
                
                $failedStatuses = array(
                    Api::PAYMENTSTATUS_FAILED,
                    Api::PAYMENTSTATUS_EXPIRED, 
                    Api::PAYMENTSTATUS_DENIED, 
                    Api::PAYMENTSTATUS_CANCELED_REVERSAL
                );
                if (in_array($paymentStatus, $failedStatuses)) {
                    $request->markFailed();
                
                    return;
                }

                $completedStatuses = array(
                    Api::PAYMENTSTATUS_COMPLETED, 
                    Api::PAYMENTSTATUS_PROCESSED
                );
                if (in_array($paymentStatus, $completedStatuses)) {
                    $successCounter++;
                }
            }
            
            if ($successCounter === $allCounter) {
                $request->markSuccess();
                
                return;
            }
        }
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

        return isset($model['PAYMENTREQUEST_0_AMT']) && null !== $model['PAYMENTREQUEST_0_AMT'];
    }
}