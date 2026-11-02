<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\Symfony\Validator\Constraints;

use Datetime;
use Payum\Core\Bridge\Symfony\Validator\Constraints\CreditCardDate;
use Payum\Core\Bridge\Symfony\Validator\Constraints\CreditCardDateValidator;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class CreditCardDateValidatorTest extends ConstraintValidatorTestCase
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

    public function testValidateWrongDateWithNamedArguments(): void
    {
        $constraint = new CreditCardDate(min: 'today', minMessage: 'theMinMessage');

        $this->validator->validate(new Datetime('1981-08-24'), $constraint);

        $this->buildViolation('theMinMessage')
            ->atPath('property.path.expireAt')
            ->assertRaised();
    }

    public function testThrowsWithoutMin(): void
    {
        $this->expectException(MissingOptionsException::class);

        new CreditCardDate();
    }

    protected function createValidator(): CreditCardDateValidator
    {
        return new CreditCardDateValidator();
    }
}
