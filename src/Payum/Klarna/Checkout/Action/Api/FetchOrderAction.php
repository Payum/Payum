<?php
namespace Payum\Klarna\Checkout\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;
use Payum\Klarna\Checkout\Request\Api\FetchOrder;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class FetchOrderAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request FetchOrder */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['location']) {
            throw new LogicException('Location has to be provided to fetch an order');
        }

        $order = new \Klarna_Checkout_Order($this->api, $model['location']);
        $order->fetch();

        $request->setOrder($order);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FetchOrder;
    }
}