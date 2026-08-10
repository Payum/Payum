<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * Something the customer must do before the operation can finish.
 *
 * This is what replaces v1's thrown Reply exceptions. The set is closed in practice -- core ships every
 * implementation -- and each one describes *intent* only. Converting a Redirect into an HTTP 302, or a
 * RenderTemplate into a rendered page, is a bridge's job, which is why core needs no HTTP framework.
 *
 * Because it is data rather than a response, a JSON API can serialise it straight to a mobile client.
 *
 * A null NextAction on a Result means the operation is finished, one way or another.
 */
interface NextAction
{
}
