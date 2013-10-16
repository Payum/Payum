<?php


namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
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

        $paymentId = $request->getModel()->getId();
        $payment = Payment::get($paymentId);

        $request->getModel()->fromArray($payment->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof SyncRequest &&
            $request->getModel() instanceof Payment
        ;
    }
}