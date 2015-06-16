<?php


namespace Payum\Core\Bridge\Symfony\Event;

use Payum\Core\Extension\Context;
use Symfony\Component\EventDispatcher\Event;


/**
 * Class ExecuteEvent
 * 
 * @package Payum\Core\Bridge\Symfony\Event
 */
class ExecuteEvent extends Event
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     */
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
