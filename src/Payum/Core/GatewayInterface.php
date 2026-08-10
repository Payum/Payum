<?php

namespace Payum\Core;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Result\Result;

interface GatewayInterface
{
    /**
     * @param CommandInterface<Result>|mixed   $request
     * @param bool $catchReply If false the reply behave like an exception. If true the reply will be caught internally and returned.
     *
     * @throws RequestNotSupportedException If there is not an action which able to process the request.
     * @throws ReplyInterface Gateway throws reply if some external tasks have to be done. For example show a credit card form, an iframe or perform a redirect.
     *
     * The return type forks with the argument, which is the price of keeping one entry point for both
     * generations: a command is answered with a Result, while a v1 request still signals by throwing a
     * reply (or returning it, when $catchReply is true). Narrowing a Result further -- CaptureCommand to
     * CaptureResult -- needs a DynamicMethodReturnTypeExtension reading the result type each command
     * declares on CommandInterface, which arrives with the executor.
     *
     * @return ($request is CommandInterface<Result> ? Result : ReplyInterface|null)
     */
    public function execute($request, $catchReply = false);
}
