<?php
namespace Payum\Klarna\Checkout\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class UpdateOrderAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateOrder */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $order = new \Klarna_Checkout_Order($this->api, $model['location']);

        $data = $model->toUnsafeArray();
        unset($data['location']);

        $order->update($data);
        $order->fetch();

        $request->setOrder($order);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof UpdateOrder;
    }
}