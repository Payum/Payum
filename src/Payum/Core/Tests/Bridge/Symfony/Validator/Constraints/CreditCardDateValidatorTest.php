<?php
namespace Payum\Core\Tests\Bridge\Symfony\Validator\Constraints;

use Payum\Core\Bridge\Symfony\Validator\Constraints\CreditCardDate;
use Payum\Core\Bridge\Symfony\Validator\Constraints\CreditCardDateValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CreditCardDateValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new CreditCardDateValidator();
    }

    public function testValidate()
    {
        $options = array('min' => 'today');
        $constraint = new CreditCardDate($options);

        $value = new \Datetime();

        $this->assertNull($this->validator->validate($value, $constraint));
    }

    public function testValidateWrongDate()
    {
        $options = array('min' => 'today');
        $constraint = new CreditCardDate($options);

        $validator = new CreditCardDateValidator();
        $validator->initialize($this->context);

        $value = new \Datetime("1981-08-24");

        $validator->validate($value, $constraint);

        $this->buildViolation('validator.credit_card.invalidDate')
            ->atPath('property.path.expireAt')
            ->assertRaised();
    }
}
