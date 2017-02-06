<?php
namespace Payum\Klarna\Checkout\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Checkout\Request\Api\FetchOrder;

class FetchOrderAction extends BaseApiAwareAction
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
            $order = new \Klarna_Checkout_Order($this->getConnector(), $model['location']);
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
