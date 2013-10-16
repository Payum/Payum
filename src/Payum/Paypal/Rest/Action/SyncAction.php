<?php


namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use Payum\Action\PaymentAwareAction;
use Payum\Exception\RequestNotSupportedException;
use Payum\Request\SyncRequest;

class SyncAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request SyncRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        if (
            true == isset($request->getModel()->state) &&
            'approved' == $request->getModel()->getState()
        ) {
            $request->getModel()->fromArray($request->getModel()->toArray());
            return;
        }


        //$paymentId = $request->getModel()->getId();
        //$payment = Payment::get($paymentId);
        $payment = $request->getModel();

    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof SyncRequest) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof Payment) {
            return false;
        }

        return isset($model->id) && null !== $model->id;
    }
}