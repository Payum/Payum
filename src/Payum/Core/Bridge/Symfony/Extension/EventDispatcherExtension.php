<?php

namespace Payum\Core\Bridge\Symfony\Extension;

use Payum\Core\Bridge\Symfony\Event\ExecuteEvent;
use Payum\Core\Bridge\Symfony\PayumEvents;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDispatcherExtension implements ExtensionInterface
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onPreExecute(Context $context)
    {
        $event = new ExecuteEvent($context);
        $this->dispatcher->dispatch($event, PayumEvents::GATEWAY_PRE_EXECUTE);
    }

    public function onExecute(Context $context)
    {
        $event = new ExecuteEvent($context);
        $this->dispatcher->dispatch($event, PayumEvents::GATEWAY_EXECUTE);
    }

    public function onPostExecute(Context $context)
    {
        $event = new ExecuteEvent($context);
        $this->dispatcher->dispatch($event, PayumEvents::GATEWAY_POST_EXECUTE);
    }
}
