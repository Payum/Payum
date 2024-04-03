<?php

namespace Payum\Core\Bridge\Symfony\Extension;

use Payum\Core\Bridge\Symfony\Event\ExecuteEvent;
use Payum\Core\Bridge\Symfony\PayumEvents;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

@trigger_error('The '.__NAMESPACE__.'\EventDispatcherExtension class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class EventDispatcherExtension implements ExtensionInterface
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onPreExecute(Context $context): void
    {
        $event = new ExecuteEvent($context);
        $this->dispatcher->dispatch($event, PayumEvents::GATEWAY_PRE_EXECUTE);
    }

    public function onExecute(Context $context): void
    {
        $event = new ExecuteEvent($context);
        $this->dispatcher->dispatch($event, PayumEvents::GATEWAY_EXECUTE);
    }

    public function onPostExecute(Context $context): void
    {
        $event = new ExecuteEvent($context);
        $this->dispatcher->dispatch($event, PayumEvents::GATEWAY_POST_EXECUTE);
    }
}
