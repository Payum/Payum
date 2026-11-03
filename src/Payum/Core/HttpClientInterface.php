<?php

namespace Payum\Core;

use Payum\Core\Legacy\LegacyFactoryScope;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function trigger_deprecation;

// Core's default payum.http_client for a 1.x factory loads this; only a load from outside that is reported.
if (! LegacyFactoryScope::isActive()) {
    trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated and will be removed in 3.0. Use Psr\Http\Client\ClientInterface instead.', HttpClientInterface::class);
}

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
