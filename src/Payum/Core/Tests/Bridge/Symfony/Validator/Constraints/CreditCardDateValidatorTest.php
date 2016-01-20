<?php
namespace Payum\Core\Tests\Bridge\Symfony\Validator\Constraints;

use Payum\Core\Bridge\Symfony\Validator\Constraints\CreditCardDate;
use Payum\Core\Bridge\Symfony\Validator\Constraints\CreditCardDateValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Validation;

class CreditCardDateValidatorTest extends AbstractConstraintValidatorTest
{
    protected function getApiVersion()
    {
        return Validation::API_VERSION_2_5;
    }
    protected function createValidator()
    {
        return new CreditCardDateValidator();
    }

    public function testValidate()
    {
        $options = array('min' => 'today');
        $constraint = new CreditCardDate($options);

        $value = new \Datetime();

        $this->validator->validate($value, $constraint);
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

    public function tearDown()
    {
        $this->constraint = null;
    }
}
