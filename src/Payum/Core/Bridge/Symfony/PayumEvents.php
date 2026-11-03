<?php

namespace Payum\Core\Bridge\Symfony;

use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\PayumEvents from payum/payum-bundle, or listen for the Payum\Core\Event\Event subclasses through a PSR-14 dispatcher, instead.', PayumEvents::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\PayumEvents from payum/payum-bundle, or
 *             listen for the Payum\Core\Event\Event subclasses through a PSR-14 dispatcher, instead.
 */
final class PayumEvents
{
    public const GATEWAY_PRE_EXECUTE = 'payum.gateway.pre_execute';

    public const GATEWAY_EXECUTE = 'payum.gateway.execute';

    public const GATEWAY_POST_EXECUTE = 'payum.gateway.post_execute';
}
