<?php
namespace Payum\Core;

interface GatewayInterface
{
    /**
     * @param mixed   $request
     * @param boolean $catchReply If false the reply behave like an exception. If true the reply will be caught internally and returned.
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException If there is not an action which able to process the request.
     * @throws \Payum\Core\Reply\ReplyInterface                   Gateway throws reply if some external tasks have to be done. For example show a credit card form, an iframe or perform a redirect.
     *
     * @return \Payum\Core\Reply\ReplyInterface|null
     */
    public function execute($request, $catchReply = false);
}
