<?php
namespace Payum\Core\Extension;

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
     * @var \Exception|null
     */
    protected $exception;

    /**
     * @var Context[]
     */
    protected $previous;

    /**
     * @param GatewayInterface $gateway
     * @param $request
     * @param Context[] $previous
     */
    public function __construct(GatewayInterface $gateway, $request, array $previous)
    {
        $this->gateway = $gateway;
        $this->request = $request;
        $this->previous = $previous;
    }

    /**
     * @return ActionInterface
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param ActionInterface $action
     */
    public function setAction(ActionInterface $action)
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

    /**
     * @param null|ReplyInterface $reply
     */
    public function setReply(ReplyInterface $reply = null)
    {
        $this->reply = $reply;
    }

    /**
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Exception|null $exception
     */
    public function setException(\Exception $exception = null)
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
