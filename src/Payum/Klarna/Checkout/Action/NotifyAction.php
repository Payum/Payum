<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\PaymentAwareAction;

class NotifyAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    function execute($request)
    {
        if (Constants::STATUS_CHECKOUT_COMPLETE == $model['status']) {
            $this->payment->execute(new UpdateOrderRequest(array(
                'location' => $model['location'],
                'status' => Constants::STATUS_CREATED,
                'merchant_reference' => array(
                    'orderid1' => $model['order_id'] ?: uniqid()
                ),
            )));

            $this->payment->execute(new SyncRequest($model));
        }    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    function supports($request)
    {
        // TODO: Implement supports() method.
    }
}