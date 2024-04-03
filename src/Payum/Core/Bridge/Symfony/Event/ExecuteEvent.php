<?php

namespace Payum\Core\Bridge\Symfony\Event;

use Payum\Core\Extension\Context;
use Symfony\Contracts\EventDispatcher\Event;

@trigger_error('The '.__NAMESPACE__.'\ExecuteEvent class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
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
