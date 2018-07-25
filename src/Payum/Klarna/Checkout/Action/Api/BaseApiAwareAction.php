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

        \Klarna_Checkout_Order::$contentType = $this->config->contentType;
        \Klarna_Checkout_Order::$baseUri = $this->config->baseUri;
        if (property_exists('Klarna_Checkout_Order', 'accept')) {
            \Klarna_Checkout_Order::$accept = $this->config->acceptHeader;
        }

        return \Klarna_Checkout_Connector::create($this->config->secret);
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
     * @param int $maxRetry
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
