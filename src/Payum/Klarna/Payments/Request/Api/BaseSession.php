<?php
namespace Payum\Klarna\Payments\Request\Api;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Request\Generic;
use Payum\Klarna\Payments\Model\Session;

abstract class BaseSession extends Generic
{
    /**
     * @var Session
     */
    protected $session;

    public function __construct($model)
    {
        if (false == (is_array($model) || $model instanceof \ArrayAccess)) {
            throw new InvalidArgumentException('Given model is invalid. Should be an array or ArrayAccess instance.');
        }

        parent::__construct($model);
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $order
     */
    public function setSession(Session $order)
    {
        $this->session = $order;
    }
}
