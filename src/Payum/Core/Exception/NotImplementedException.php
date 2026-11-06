<?php

declare(strict_types=1);

namespace Payum\Core\Exception;

/**
 * A gateway declares an operation its code does not perform yet, such as a handler stub generated from
 * payum/skeleton.
 */
class NotImplementedException extends LogicException
{
    protected $message = 'Not implemented';
}
