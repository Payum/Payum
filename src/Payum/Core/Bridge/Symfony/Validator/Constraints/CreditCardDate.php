<?php
/**
 * @author Marc Pantel <pantel.m@gmail.com>
 */
namespace Payum\Core\Bridge\Symfony\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * CreditCardDate
 */
class CreditCardDate extends Constraint
{
    public $minMessage = 'validator.credit_card.invalidDate';
    public $invalidMessage = 'validator.credit_card.invalidDate';
    public $min;

    public function __construct($options = null)
    {
        parent::__construct($options);

        if (null === $this->min) {
            throw new MissingOptionsException('Either option "min" must be given for constraint ' . __CLASS__, array('min'));
        }

        if (null !== $this->min) {
            $this->min = new \DateTime($this->min);
            $this->min->modify('last day of this month');
        }
    }
}
