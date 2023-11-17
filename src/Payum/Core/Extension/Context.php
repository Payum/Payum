<?php

namespace Payum\Core\Extension;

use Exception;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\ReplyInterface;

class Context
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * @var mixed
     */
    protected $request;

    /**
     * @var ActionInterface
     */
    protected $action;

    /**
     * @var ReplyInterface|null
     */
    protected $reply;

    /**
     * @var Exception|null
     */
    protected $exception;

    /**
     * @var Context[]
     */
    protected $previous;

    /**
     * @param Context[] $previous
     */
    public function __construct(GatewayInterface $gateway, $request, array $previous)
    {
        $this->gateway = $gateway;
        $this->request = $request;
        $this->previous = $previous;
    }

    /**
     * @return ?ActionInterface
     */
    public function getAction()
    {
        return $this->action;
    }

    public function setAction(ActionInterface $action): void
    {
        $this->action = $action;
    }

    /**
     * @return null|ReplyInterface
     */
    public function getReply()
    {
        return $this->reply;
    }

    public function setReply(ReplyInterface $reply = null): void
    {
        $this->reply = $reply;
    }

    /**
     * @return Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    public function setException(Exception $exception = null): void
    {
        $this->exception = $exception;
    }

    /**
     * @return GatewayInterface
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @return Context[]
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }
}
