<?php

namespace Payum\Klarna\Checkout\Action\Api;

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
     * @var \Klarna_Checkout_ConnectorInterface
     */
    private $connector;

    public function __construct(\Klarna_Checkout_ConnectorInterface $connector = null)
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
     * @return \Klarna_Checkout_ConnectorInterface
     */
    protected function getConnector()
    {
        if ($this->connector) {
            return $this->connector;
        }

        return \Klarna_Checkout_Connector::create($this->config->secret);
    }

    /**
     * @param \Klarna_Checkout_ConnectorInterface $connector
     *
     * @return \Klarna_Checkout_Order
     */
    protected function getOrder(\Klarna_Checkout_ConnectorInterface $connector): \Klarna_Checkout_Order
    {
        $klarnaCheckoutOrder = new \Klarna_Checkout_Order($connector);
        $klarnaCheckoutOrder->setContentType($this->config->contentType);
        $klarnaCheckoutOrder->setLocation($this->config->baseUri);
        if (property_exists('Klarna_Checkout_Order', 'accept')) {
            $klarnaCheckoutOrder->setAccept($this->config->acceptHeader);
        }

        return $klarnaCheckoutOrder;
    }

    /**
     * @param \ArrayAccess $details
     */
    protected function addMerchantId(\ArrayAccess $details)
    {
        if (false == isset($details['merchant'])) {
            $details['merchant'] = array();
        }

        $merchant = $details['merchant'];
        if (false == isset($merchant['id'])) {
            $merchant['id'] = (string) $this->config->merchantId;
        }

        $details['merchant'] = $merchant;
    }

    /**
     * @param \Closure $function
     * @param int      $maxRetry
     *
     * @throws \Klarna_Checkout_ConnectionErrorException
     *
     * @return mixed
     */
    protected function callWithRetry(\Closure $function, $maxRetry = 3)
    {
        $attempts = 1;
        while (true) {
            try {
                return call_user_func($function);
            } catch (\Klarna_Checkout_ConnectionErrorException $e) {
                if ($attempts >= $maxRetry) {
                    throw $e;
                }

                $attempts++;
            }
        }
    }
}
