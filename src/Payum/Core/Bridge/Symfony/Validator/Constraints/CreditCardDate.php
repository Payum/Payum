<?php
/**
 * @author Marc Pantel <pantel.m@gmail.com>
 */

namespace Payum\Core\Bridge\Symfony\Validator\Constraints;

use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

@trigger_error('The '.__NAMESPACE__.'\CreditCardDate class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
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
            throw new MissingOptionsException('Either option "min" must be given for constraint ' . self::class, ['min']);
        }

        if (null !== $this->min) {
            $this->min = new DateTime($this->min);
            $this->min->modify('last day of this month');
        }
    }
}
