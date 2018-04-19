<?php
namespace Payum\Klarna\CheckoutRest\Action\Api;

use Klarna\Rest\OrderManagement\Order;
use Klarna\Rest\Transport\Connector;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\CheckoutRest\Request\Api\UpdateOrder;
use Payum\Klarna\Common\Action\Api\BaseApiAwareAction;

class UpdateOrderAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param UpdateOrder $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->callWithRetry(function () use ($model, $request) {
            $order = $this->getOrderManagementOrder($this->getConnector(), $model['order_id']);

            $request->setOrder($order);
            $request->execute();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof UpdateOrder;
    }

    /**
     * @param Connector $connector
     * @param string    $orderId
     *
     * @return Order
     */
    private function getOrderManagementOrder(Connector $connector, string $orderId)
    {
        return new Order($connector, $orderId);
    }
}
