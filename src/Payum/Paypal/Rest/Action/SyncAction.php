<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;

class SyncAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Payment $model */
        $model = $request->getModel();

        $payment = Payment::get($model->id);

        $model->fromArray($payment->toArray());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof Payment
        ;
    }
}
