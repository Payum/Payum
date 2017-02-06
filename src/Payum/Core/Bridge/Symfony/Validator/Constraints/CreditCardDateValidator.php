<?php
/**
 * @author Marc Pantel <pantel.m@gmail.com>
 */
namespace Payum\Core\Bridge\Symfony\Validator\Constraints;

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
            if (method_exists($this->context, 'buildViolation')) {
                $this->context->buildViolation($constraint->invalidMessage, array(
                             '{{ value }}' => $value,
                         ))
                     ->addViolation();

                return;
            }

            $this->context->addViolationAt('expireAt', $constraint->invalidMessage, array(
                '{{ value }}' => $value,
            ));
        }

        /**
         * The Credit Card is not expired until last day of the month
         */
        $value->modify('last day of this month');

        if (null !== $constraint->min && $value < $constraint->min) {
            if (method_exists($this->context, 'buildViolation')) {
                $this->context->buildViolation($constraint->minMessage)
                    ->atPath('expireAt')
                    ->addViolation();

                return;
            }

            $this->context->addViolationAt('expireAt', $constraint->minMessage);
        }
    }
}
