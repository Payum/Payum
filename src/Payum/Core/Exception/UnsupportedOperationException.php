<?php

declare(strict_types=1);

namespace Payum\Core\Exception;

/**
 * The gateway has not declared the capability a command needs. Nothing was sent to the PSP.
 */
class UnsupportedOperationException extends LogicException
{
}
