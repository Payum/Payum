<?php
namespace Payum\Klarna\Checkout\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var \Klarna_Checkout_Connector
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof \Klarna_Checkout_ConnectorInterface) {
            throw new UnsupportedApiException('Not supported. Expected Klarna_Checkout_ConnectorInterface instance to be set as api.');
        }

        $this->api = $api;
    }
}