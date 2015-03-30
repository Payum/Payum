<?php
/**
 * @author Marc Pantel <pantel.m@gmail.com>
 */
namespace Payum\Bundle\PayumBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * CreditCardDateValidator
 *
 * Validate if the Credit Card is not expired
 */
class CreditCardDateValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!($value instanceof \DateTime)) {
            $this->context->addViolation($constraint->invalidMessage, array(
                '{{ value }}' => $value,
            ));

            return;
        }

        $value->modify('last day of this month');

        if (null !== $constraint->min && $value < $constraint->min) {
            $this->context->buildViolation($constraint->minMessage)
                ->atPath('expireAt')
                ->addViolation();
        }
    }
}
