<?php
namespace Payum\Klarna\CheckoutRest\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\CheckoutRest\Request\Api\FetchOrder;

class FetchOrderAction extends BaseOrderAction
{
    /**
     * {@inheritDoc}
     *
     * @param FetchOrder $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['location']) {
            throw new LogicException('Location has to be provided to fetch an order');
        }

        $this->callWithRetry(function () use ($model, $request) {
            $order = $this->getOrder($this->getConnector());
            $order->setLocation($model['location']);
            $order->fetch();

            $request->setOrder($order);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FetchOrder;
    }
}
