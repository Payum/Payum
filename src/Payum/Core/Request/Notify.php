<?php

namespace Payum\Core\Request;

use Payum\Core\Command\NotifyCommand;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s request is deprecated and will be removed in 3.0. Dispatch %s instead.', Notify::class, NotifyCommand::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Dispatch Payum\Core\Command\NotifyCommand instead.
 */
class Notify extends Generic
{
}
