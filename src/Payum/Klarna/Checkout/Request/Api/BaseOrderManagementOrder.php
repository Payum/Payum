<?php


namespace Payum\Klarna\Checkout\Request\Api;

use Klarna\Rest\OrderManagement\Order;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Request\Generic;

class BaseOrderManagementOrder extends Generic
{

    /**
     * @var Order|null
     */
    private $order;

    public function __construct($model)
    {
        if (false == (is_array($model) || $model instanceof \ArrayAccess)) {
            throw new InvalidArgumentException('Given model is invalid. Should be an array or ArrayAccess instance.');
        }

        parent::__construct($model);
    }

    /**
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return BaseOrderManagementOrder
     */
    public function setOrder(Order $order): BaseOrderManagementOrder
    {
        $this->order = $order;

        return $this;
    }

}