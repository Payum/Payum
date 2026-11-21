<?php

declare(strict_types=1);

namespace Payum\Core\Reference;

use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\UndescribedGateway;

final class ReferenceNamespace
{
    public static function of(Context $context): string
    {
        return self::forGateway($context->gateway());
    }

    /**
     * A 1.x gateway has no descriptor class of its own, so its name stands in.
     */
    public static function forGateway(GatewayInterface $gateway): string
    {
        return $gateway instanceof UndescribedGateway ? $gateway->name() : $gateway::class;
    }
}
