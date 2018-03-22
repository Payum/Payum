<?php

namespace Payum\Klarna\Checkout\Action\Api;

use Klarna\Rest\Checkout\Order;
use Klarna\Rest\Transport\Connector;
use Klarna\Rest\Transport\Exception\ConnectorException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Klarna\Checkout\Config;

abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait {
        setApi as _setApi;
    }

    /**
     * @deprecated BC. will be removed in 2.x. Use $this->api
     *
     * @var Config
     */
    protected $config;

    /**
     * @var Connector
     */
    private $connector;

    public function __construct(Connector $connector = null)
    {
        $this->connector = $connector;

        // BC. will be removed in 2.x. Use $this->api
        $this->apiClass = Config::class;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        $this->_setApi($api);

        $this->config = $this->api;
    }

    /**
     * @return Connector
     */
    protected function getConnector()
    {
        if ($this->connector) {
            return $this->connector;
        }

        return Connector::create($this->config->merchantId, $this->config->secret, $this->config->baseUri);
    }

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

    /**
     * @param \Closure $function
     * @param int      $maxRetry
     *
     * @throws ConnectorException
     *
     * @return mixed
     */
    protected function callWithRetry(\Closure $function, $maxRetry = 3)
    {
        $attempts = 1;
        while (true) {
            try {
                return call_user_func($function);
            } catch (ConnectorException $e) {
                if ($attempts >= $maxRetry) {
                    throw $e;
                }

                $attempts++;
            }
        }
    }
}
