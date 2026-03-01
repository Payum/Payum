<?php

namespace Payum\PayTheFly\Exception;

use Payum\Core\Exception\InvalidArgumentException;

/**
 * Thrown when a webhook signature verification fails.
 */
class InvalidSignatureException extends InvalidArgumentException
{
}
