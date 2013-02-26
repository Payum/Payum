<?php
namespace Payum\OmnipayBridge\Action;

use Omnipay\Common\GatewayInterface;

use Payum\Action\ActionApiAwareInterface;
use Payum\Exception\UnsupportedApiException;

abstract class BaseActionApiAware implements ActionApiAwareInterface
{
    /**
     * @var \Omnipay\Common\GatewayInterface
     */
    protected $gateway;

    /**
     * {@inheritdoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof GatewayInterface) {
            throw new UnsupportedApiException('Not supported.');
        }
        
        $this->gateway = $api;
    }
}