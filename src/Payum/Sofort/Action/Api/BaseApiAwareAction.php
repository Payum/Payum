<?php

namespace Payum\Sofort\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Sofort\Api;

/**
 * @deprecated since 1.4.1 will be removed in 2.x
 *
 * @template T of Api
 * @implements ApiAwareInterface<T>
 */
abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var T
     */
    protected Api $api;

    public function setApi(object $api): void
    {
        if (! $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }
}
