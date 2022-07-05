<?php

namespace Payum\Core\Bridge\Symfony;

final class PayumEvents
{
    public const GATEWAY_PRE_EXECUTE = 'payum.gateway.pre_execute';

    public const GATEWAY_EXECUTE = 'payum.gateway.execute';

    public const GATEWAY_POST_EXECUTE = 'payum.gateway.post_execute';
}
