<?php
namespace Payum\Core\Bridge\Symfony\Extension;

use Payum\Core\Bridge\Symfony\Event\ExecuteEvent;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Payum\Core\Bridge\Symfony\PayumEvents;

class EventDispatcherExtension implements ExtensionInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute(Context $context)
    {
        $event = new ExecuteEvent($context);
        $this->dispatcher->dispatch($event, PayumEvents::GATEWAY_PRE_EXECUTE);
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context)
    {
        $event = new ExecuteEvent($context);
        $this->dispatcher->dispatch($event, PayumEvents::GATEWAY_EXECUTE);
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context)
    {
        $event = new ExecuteEvent($context);
        $this->dispatcher->dispatch($event, PayumEvents::GATEWAY_POST_EXECUTE);
    }
}
