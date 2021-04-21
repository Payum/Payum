<?php
namespace Payum\Core\Bridge\Symfony;

final class PayumEvents
{
    const GATEWAY_PRE_EXECUTE = 'payum.gateway.pre_execute';

    const GATEWAY_EXECUTE = 'payum.gateway.execute';

    const GATEWAY_POST_EXECUTE = 'payum.gateway.post_execute';
}
