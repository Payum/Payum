<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use Payum\Core\Request\Generic;

/**
 * A gateway's own request, dispatched from inside its capture.
 */
final class AwkwardRedirect extends Generic
{
}
