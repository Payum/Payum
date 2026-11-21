<?php

declare(strict_types=1);

namespace Payum\Core\Reference;

use Payum\Core\Handler\Context;
use Payum\Core\Legacy\UndescribedGateway;

final class ReferenceNamespace
{
    /**
     * A 1.x gateway has no descriptor class of its own, so its name stands in.
     */
    public static function of(Context $context): string
    {
        $gateway = $context->gateway();

        return $gateway instanceof UndescribedGateway ? $gateway->name() : $gateway::class;
    }
}
