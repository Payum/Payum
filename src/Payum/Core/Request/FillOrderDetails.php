<?php
namespace Payum\Core\Request;

use Payum\Core\Model\OrderInterface;
use Payum\Core\Security\TokenInterface;

class FillOrderDetails
{
    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var TokenInterface|null
     */
    protected $token;

    /**
     * @var array
     */
    protected $details;

    /**
     * @param OrderInterface $order
     * @param TokenInterface $token
     */
    public function __construct(OrderInterface $order, TokenInterface $token = null)
    {
        $this->order = $order;
        $this->token = $token;
        $this->details = array();
    }

    /**
     * @return OrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return null|TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }
}
