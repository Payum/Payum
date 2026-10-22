<?php

namespace Payum\Core\Reply;

use Payum\Core\Exception\LogicException;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A handler returns a Payum\Core\Result\Result
 *             carrying a Payum\Core\Result\NextAction instead of throwing a reply.
 */
abstract class Base extends LogicException implements ReplyInterface
{
}
