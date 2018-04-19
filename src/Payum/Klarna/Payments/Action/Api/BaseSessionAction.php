<?php

namespace Payum\Klarna\Checkout\Action\Api;

use Klarna\Rest\Transport\Connector;
use Payum\Klarna\Common\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Payments\Model\Session;

/**
 * TODO: Add description
 *
 * @author Oscar Reimer <oscar.reimer@eit.lth.se>
 */
abstract class BaseSessionAction extends BaseApiAwareAction
{

    /**
     * @param Connector $connector
     *
     * @return Session
     */
    protected function getSession(Connector $connector): Session
    {
        $klarnaCheckoutOrder = new Session($connector);
        $klarnaCheckoutOrder->setLocation($this->config->baseUri);

        return $klarnaCheckoutOrder;
    }

}