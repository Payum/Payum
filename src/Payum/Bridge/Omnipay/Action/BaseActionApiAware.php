<?php
namespace Payum\Bridge\Omnipay\Action;

use Omnipay\Common\GatewayInterface;

use Payum\Action\ActionInterface;
use Payum\ApiAwareInterface;
use Payum\Exception\UnsupportedApiException;

abstract class BaseActionApiAware implements ActionInterface, ApiAwareInterface
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