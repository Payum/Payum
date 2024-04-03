<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use function trigger_error;

/**
 * @deprecated since 1.4.1 will be removed in 3.x
 */
abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    public function setApi($api): void
    {
        @trigger_error('The ' . self::class . '::setApi is deprecated since 1.4.1 and will be removed in 3.x.', E_USER_DEPRECATED);

        if (! $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }
}
