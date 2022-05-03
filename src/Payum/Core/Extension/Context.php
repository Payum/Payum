<?php
namespace Payum\Core\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\ReplyInterface;

class Context
{
    protected ActionInterface $action;
    protected ?ReplyInterface $reply;

    /**
     * @param Context[] $previous
     */
    public function __construct(protected GatewayInterface $gateway, protected mixed $request, protected array $previous)
    {
    }

    public function getAction(): ActionInterface
    {
        return $this->action;
    }

    public function setAction(ActionInterface $action): void
    {
        $this->action = $action;
    }

    public function getReply(): ?ReplyInterface
    {
        return $this->reply;
    }

    public function setReply(ReplyInterface $reply = null): void
    {
        $this->reply = $reply;
    }

    public function getException(): ?\Exception
    {
        return $this->exception;
    }

    public function setException(\Exception $exception = null): void
    {
        $this->exception = $exception;
    }

    public function getGateway(): GatewayInterface
    {
        return $this->gateway;
    }

    /**
     * @return Context[]
     */
    public function getPrevious(): array
    {
        return $this->previous;
    }

    public function getRequest(): mixed
    {
        return $this->request;
    }
}
