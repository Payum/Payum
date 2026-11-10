<?php

declare(strict_types=1);

namespace Payum\Core\Exception;

/**
 * The caller sent an idempotency key it had already used for a different operation.
 *
 * A key names one operation. Using it again for another amount, another payment or another kind of
 * command is a bug in the caller, and answering with the first result would hide it.
 */
class IdempotencyKeyReusedException extends LogicException
{
}
