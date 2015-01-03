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

    /**
     * @return ActionInterface
     */
    public function getAction()
    {
        return $this->action;
    }
}
