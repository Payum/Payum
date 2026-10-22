<?php

namespace Payum\Core\Request;

use Payum\Core\Command\AuthorizeCommand;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s request is deprecated and will be removed in 3.0. Dispatch %s instead.', Authorize::class, AuthorizeCommand::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Dispatch Payum\Core\Command\AuthorizeCommand instead.
 */
class Authorize extends Generic
{
}
