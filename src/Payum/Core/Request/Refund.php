<?php

namespace Payum\Core\Request;

use Payum\Core\Command\RefundCommand;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s request is deprecated and will be removed in 3.0. Dispatch %s instead.', Refund::class, RefundCommand::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Dispatch Payum\Core\Command\RefundCommand instead.
 */
class Refund extends Generic
{
}
