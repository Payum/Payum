<?php

namespace Payum\Core\Request;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\CreditCardInterface;

class ObtainCreditCard extends Generic
{
    protected ?CreditCardInterface $creditCard = null;

    public function __construct(?object $firstModel = null, ?object $currentModel = null)
    {
        parent::__construct($firstModel);

        $this->setModel($currentModel);
    }

    public function set(CreditCardInterface $creditCard): void
    {
        $this->creditCard = $creditCard;
    }

    public function obtain(): ?CreditCardInterface
    {
        if (! $this->creditCard) {
            throw new LogicException('Credit card could not be obtained. It has to be set before obtain.');
        }

        return $this->creditCard;
    }
}
