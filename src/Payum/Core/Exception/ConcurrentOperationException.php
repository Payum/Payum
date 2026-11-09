<?php

declare(strict_types=1);

namespace Payum\Core\Exception;

/**
 * Another execution held the payment for longer than this one was prepared to wait.
 *
 * Nothing reached the PSP, so the command is safe to retry.
 */
class ConcurrentOperationException extends RuntimeException
{
}
