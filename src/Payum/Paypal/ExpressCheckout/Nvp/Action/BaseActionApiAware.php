<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Action\ActionApiAwareInterface;
use Payum\Exception\UnsupportedApiException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

abstract class BaseActionApiAware implements ActionApiAwareInterface
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