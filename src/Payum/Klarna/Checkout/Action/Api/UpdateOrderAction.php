<?php

namespace Payum\Klarna\Checkout\Action\Api;

use Klarna_Checkout_Order;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class UpdateOrderAction extends BaseApiAwareAction
{
    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->callWithRetry(function () use ($model, $request): void {
            $order = new Klarna_Checkout_Order($this->getConnector(), $model['location']);

            $data = $model->toUnsafeArray();
            unset($data['location']);

            $order->update($data);

            $request->setOrder($order);
        });
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof UpdateOrder;
    }
}
