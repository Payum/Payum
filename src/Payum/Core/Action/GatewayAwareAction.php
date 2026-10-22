<?php

namespace Payum\Core\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Handler\Context;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Implement %s and use %s on the action itself, or port it to a handler and dispatch through %s::execute().', GatewayAwareAction::class, GatewayAwareInterface::class, GatewayAwareTrait::class, Context::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Implement Payum\Core\GatewayAwareInterface and use
 *             Payum\Core\GatewayAwareTrait on the action itself, or port it to a handler and dispatch
 *             through Payum\Core\Handler\Context::execute().
 */
abstract class GatewayAwareAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
}
