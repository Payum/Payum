<?php
namespace Payum\Core;

interface GatewayInterface
{
    /**
     * @param mixed   $request
     * @param boolean $catchReply
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException if there is not an action which able to process the request.
     * @throws \Payum\Core\Reply\ReplyInterface                   when a gateway needs some external tasks to be executed. like a redirect to a gateway site or a page with credit card form. if $catchReply set to false the reply will be returned.
     *
     * @return \Payum\Core\Reply\ReplyInterface|null
     */
    public function execute($request, $catchReply = false);
}
