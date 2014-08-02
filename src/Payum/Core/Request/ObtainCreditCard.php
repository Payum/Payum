<?php
namespace Payum\Core\Request;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\CreditCardInterface;

class ObtainCreditCard
{
    /**
     * @var CreditCardInterface
     */
    protected $creditCard;

    /**
     * @param CreditCardInterface $creditCard
     */
    public function set(CreditCardInterface $creditCard)
    {
        $this->creditCard = $creditCard;
    }

    /**
     * @return CreditCardInterface
     */
    public function obtain()
    {
        if (false == $this->creditCard) {
            throw new LogicException('Credit card could not be obtained. It has to be set before obtain.');
        }

        return $this->creditCard;
    }
}
