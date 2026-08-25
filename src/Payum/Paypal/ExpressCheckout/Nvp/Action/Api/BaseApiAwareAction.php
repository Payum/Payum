<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

/**
 * @deprecated since 1.4.1, will be removed in 3.0. Take the
 *             Payum\Paypal\ExpressCheckout\Nvp\Api as a constructor argument instead, and let the
 *             container inject it.
 */
abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @deprecated since 1.4.1, will be removed in 3.0. Take the
     *             Payum\Paypal\ExpressCheckout\Nvp\Api as a constructor argument instead, and let the
     *             container inject it.
     */
    public function setApi($api): void
    {
        if (! $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }
}
