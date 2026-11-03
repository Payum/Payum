<?php

namespace Payum\Core\Bridge\Symfony\Event;

use Payum\Core\Extension\Context;
use Symfony\Contracts\EventDispatcher\Event;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Event\ExecuteEvent from payum/payum-bundle, or listen for the Payum\Core\Event\Event subclasses through a PSR-14 dispatcher, instead.', ExecuteEvent::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Event\ExecuteEvent from payum/payum-bundle, or
 *             listen for the Payum\Core\Event\Event subclasses through a PSR-14 dispatcher, instead.
 */
class ExecuteEvent extends Event
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }
}
