<?php

namespace Payum\Core;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\ReplyInterface;

interface GatewayInterface
{
    /**
     * @throws RequestNotSupportedException If there is not an action which able to process the request.
     * @throws ReplyInterface Gateway throws reply if some external tasks have to be done. For example show a credit card form, an iframe or perform a redirect.
     */
    public function execute(mixed $request, bool $catchReply = false): ?ReplyInterface;
}
