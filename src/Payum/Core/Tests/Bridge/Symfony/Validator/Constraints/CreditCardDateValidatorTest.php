<?php

namespace Payum\Core\Tests\Bridge\Symfony\Validator\Constraints;

use Datetime;
use Payum\Core\Bridge\Symfony\Validator\Constraints\CreditCardDate;
use Payum\Core\Bridge\Symfony\Validator\Constraints\CreditCardDateValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CreditCardDateValidatorTest extends ConstraintValidatorTestCase
{
    public function testValidate(): void
    {
        $options = [
            'min' => 'today',
        ];
        $constraint = new CreditCardDate($options);

        $value = new Datetime();

        $this->assertNull($this->validator->validate($value, $constraint));
    }

    public function testValidateWrongDate(): void
    {
        $options = [
            'min' => 'today',
        ];
        $constraint = new CreditCardDate($options);

        $validator = new CreditCardDateValidator();
        $validator->initialize($this->context);

        $value = new Datetime('1981-08-24');

        $validator->validate($value, $constraint);

        $this->buildViolation('validator.credit_card.invalidDate')
            ->atPath('property.path.expireAt')
            ->assertRaised();
    }

    protected function createValidator(): CreditCardDateValidator
    {
        return new CreditCardDateValidator();
    }
}
