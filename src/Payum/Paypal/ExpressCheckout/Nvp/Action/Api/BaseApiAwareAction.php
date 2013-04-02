<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Action\ActionInterface;
use Payum\ApiAwareInterface;
use Payum\Exception\UnsupportedApiException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var \Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected $api;

    /**
     * {@inheritdoc}
     */
    public function setApi($api)
    {
        if (false ==$api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }
        
        $this->api = $api;
    }
}