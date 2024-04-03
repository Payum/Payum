<?php

namespace Payum\Core;

use Payum\Core\Exception\UnsupportedApiException;
use function trigger_error;

@trigger_error('The ' . __NAMESPACE__ . '\ApiAwareInterface is deprecated since 2.0.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use dependency-injection to inject the api class instead.
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
