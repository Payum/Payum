<?php

namespace Payum\Core\Bridge\Symfony;

@trigger_error('The '.__NAMESPACE__.'\PayumEvents class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
final class PayumEvents
{
    public const GATEWAY_PRE_EXECUTE = 'payum.gateway.pre_execute';

    public const GATEWAY_EXECUTE = 'payum.gateway.execute';

    public const GATEWAY_POST_EXECUTE = 'payum.gateway.post_execute';
}
