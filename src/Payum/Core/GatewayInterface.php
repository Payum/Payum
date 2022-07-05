<?php

namespace Payum\Core;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\ReplyInterface;

interface GatewayInterface
{
    /**
     * @param mixed   $request
     * @param boolean $catchReply If false the reply behave like an exception. If true the reply will be caught internally and returned.
     *
     * @throws RequestNotSupportedException If there is not an action which able to process the request.
     * @throws ReplyInterface Gateway throws reply if some external tasks have to be done. For example show a credit card form, an iframe or perform a redirect.
     *
     * @return ReplyInterface|null
     */
    public function execute($request, $catchReply = false);
}
