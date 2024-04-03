<?php
/**
 * @author Marc Pantel <pantel.m@gmail.com>
 */

namespace Payum\Core\Bridge\Symfony\Validator\Constraints;

use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

@trigger_error('The '.__NAMESPACE__.'\CreditCardDateValidator class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 *
 * Validate if the Credit Card is not expired
 */
class CreditCardDateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (! ($value instanceof DateTime)) {
            if (method_exists($this->context, 'buildViolation')) {
                $this->context->buildViolation($constraint->invalidMessage, [
                    '{{ value }}' => $value,
                ])
                    ->addViolation();

                return;
            }

            $this->context->addViolationAt('expireAt', $constraint->invalidMessage, [
                '{{ value }}' => $value,
            ]);
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
