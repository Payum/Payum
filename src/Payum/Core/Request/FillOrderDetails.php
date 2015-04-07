<?php
namespace Payum\Core\Request;

use Payum\Core\Model\PaymentInterface;
use Payum\Core\Security\TokenInterface;

/**
 * @deprecated since 0.14.3 Use Convert request
 */
class FillOrderDetails
{
    /**
     * @var PaymentInterface
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
     * @param PaymentInterface $order
     * @param TokenInterface $token
     */
    public function __construct(PaymentInterface $order, TokenInterface $token = null)
    {
        $this->order = $order;
        $this->token = $token;
        $this->details = array();
    }

    /**
     * @return PaymentInterface
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
