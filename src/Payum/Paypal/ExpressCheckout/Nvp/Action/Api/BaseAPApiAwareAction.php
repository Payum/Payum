<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Paypal\ExpressCheckout\Nvp\APApi;

abstract class BaseAPApiAwareAction implements ActionInterface, ApiAwareInterface
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
        if ($api instanceof APApi == false) {
            throw new UnsupportedApiException('Not supported.');
        }
        
        $this->api = $api;
    }
}