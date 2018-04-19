<?php

namespace Payum\Klarna\Payments\Request\Api;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Request\Generic;
use Payum\Klarna\Payments\Model\Order;

abstract class BaseOrder extends Generic
{
    /**
     * @var Order
     */
    protected $order;

    public function __construct($model)
    {
        if (false == (is_array($model) || $model instanceof \ArrayAccess)) {
            throw new InvalidArgumentException('Given model is invalid. Should be an array or ArrayAccess instance.');
        }

        parent::__construct($model);
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }
}