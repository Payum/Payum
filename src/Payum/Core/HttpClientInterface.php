<?php

namespace Payum\Core;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function trigger_error;

@trigger_error('The ' . __NAMESPACE__ . '\HttpClientInterface is deprecated since 2.0.0 and will be removed in 3.0. Use Psr\Http\Client\ClientInterface instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Psr\Http\Client\ClientInterface instead.
 */
interface HttpClientInterface
{
    /**
     * @return ResponseInterface
     */
    public function send(RequestInterface $request);
}
