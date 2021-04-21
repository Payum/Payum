<?php
namespace Payum\Klarna\CheckoutRest\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\CheckoutRest\Request\Api\CreateOrder;

class CreateOrderAction extends BaseOrderAction
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
            $order = $this->getOrder($this->getConnector());
            $order->create($model->toUnsafeArray());

            $request->setOrder($order);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof CreateOrder;
    }
}
