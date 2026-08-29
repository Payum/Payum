<?php

namespace Payum\Core\Request;

use Payum\Core\Command\PayoutCommand;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s request is deprecated and will be removed in 3.0. Dispatch %s instead.', Payout::class, PayoutCommand::class);

/**
 * Pay a large sum of money from funds under one’s control
 *
 * @deprecated since 2.0.0, will be removed in 3.0. Dispatch Payum\Core\Command\PayoutCommand instead.
 */
class Payout extends Generic
{
}
