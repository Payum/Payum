<?php

namespace Payum\Core\Reply;

use Payum\Core\Exception\ExceptionInterface;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A handler returns a Payum\Core\Result\Result
 *             carrying a Payum\Core\Result\NextAction instead of throwing a reply.
 */
interface ReplyInterface extends ExceptionInterface
{
}
