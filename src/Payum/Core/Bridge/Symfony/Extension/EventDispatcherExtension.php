<?php

namespace Payum\Core\Bridge\Symfony\Extension;

use Payum\Core\Bridge\Symfony\Event\ExecuteEvent;
use Payum\Core\Bridge\Symfony\PayumEvents;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Extension\EventDispatcherExtension from payum/payum-bundle, or listen for the Payum\Core\Event\Event subclasses through a PSR-14 dispatcher, instead.', EventDispatcherExtension::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Extension\EventDispatcherExtension from payum/payum-bundle, or
 *             listen for the Payum\Core\Event\Event subclasses through a PSR-14 dispatcher, instead.
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
