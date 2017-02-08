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
     *
     * @param CreateOrder $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->callWithRetry(function () use ($model, $request) {
            $order = new \Klarna_Checkout_Order($this->getConnector(), $model['location']);

            $data = $model->toUnsafeArray();
            unset($data['location']);

            $order->update($data);

            $request->setOrder($order);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof UpdateOrder;
    }
}
