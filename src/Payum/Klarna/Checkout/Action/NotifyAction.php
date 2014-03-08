<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\NotifyRequest;
use Payum\Core\Request\SyncRequest;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\UpdateOrderRequest;

class NotifyAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request NotifyRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->payment->execute(new SyncRequest($model));

        if (Constants::STATUS_CHECKOUT_COMPLETE == $model['status']) {
            $model->validatedNotEmpty(array('order_id'));

            $this->payment->execute(new UpdateOrderRequest(array(
                'location' => $model['location'],
                'status' => Constants::STATUS_CREATED,
                'merchant_reference' => array(
                    'orderid1' => $model['order_id'] ?: uniqid()
                ),
            )));

            $this->payment->execute(new SyncRequest($model));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof NotifyRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
