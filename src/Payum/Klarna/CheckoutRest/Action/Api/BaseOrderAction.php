<?php

namespace Payum\Klarna\CheckoutRest\Action\Api;

use Klarna\Rest\Checkout\Order;
use Klarna\Rest\Transport\Connector;
use Payum\Klarna\Common\Action\Api\BaseApiAwareAction;

/**
 * TODO: Add description
 *
 * @author Oscar Reimer <oscar.reimer@eit.lth.se>
 */
abstract class BaseOrderAction extends BaseApiAwareAction
{

    /**
     * @param Connector $connector
     *
     * @return Order
     */
    protected function getOrder(Connector $connector): Order
    {
        $klarnaCheckoutOrder = new Order($connector);
        $klarnaCheckoutOrder->setLocation($this->config->baseUri);

        return $klarnaCheckoutOrder;
    }

}