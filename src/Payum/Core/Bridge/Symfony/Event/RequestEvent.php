<?php

namespace Payum\Core\Bridge\Symfony\Event;

use Symfony\Component\EventDispatcher\Event;
use Payum\Core\Action\ActionInterface;

class RequestEvent extends Event
{
    protected $request;
    protected $action;

    public function __construct($request, ActionInterface $action = null)
    {
        $this->request = $request;
        $this->action = $action;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest($request)
    {
        $this->request = $request;
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
}
