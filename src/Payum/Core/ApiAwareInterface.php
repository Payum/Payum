<?php

namespace Payum\Core;

use Payum\Core\Exception\UnsupportedApiException;
use function trigger_error;

@trigger_error('The ' . __NAMESPACE__ . '\ApiAwareInterface is deprecated since 2.0.0 and will be removed in 3.0. Take the api as a constructor argument instead, and let the container inject it.', E_USER_DEPRECATED);

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
