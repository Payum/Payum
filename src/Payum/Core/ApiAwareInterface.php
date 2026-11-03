<?php

namespace Payum\Core;

use Payum\Core\Exception\UnsupportedApiException;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated and will be removed in 3.0. Take the api as a constructor argument instead, and let the container inject it.', ApiAwareInterface::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Take the api as a constructor argument instead, and
 *             let the container inject it.
 */
interface ApiAwareInterface
{
    /**
     * @param mixed $api
     *
     * @throws UnsupportedApiException if the given Api is not supported.
     */
    public function setApi($api);
}
