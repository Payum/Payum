<?php

namespace Payum\Core\Bridge\Symfony\Event;

use Payum\Core\Extension\Context;
use Symfony\Contracts\EventDispatcher\Event;

@trigger_error('The ' . __NAMESPACE__ . '\ExecuteEvent class is deprecated since version 2.0 and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Event\ExecuteEvent from payum/payum-bundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Event\ExecuteEvent from payum/payum-bundle instead.
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
