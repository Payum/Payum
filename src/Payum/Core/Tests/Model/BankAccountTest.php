<?php

namespace Payum\Core\Tests\Model;

use Payum\Core\Model\BankAccount;
use Payum\Core\Model\BankAccountInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BankAccountTest extends TestCase
{
    public function testShouldExtendBankAccountInterface(): void
    {
        $rc = new ReflectionClass(BankAccount::class);

        $this->assertTrue($rc->implementsInterface(BankAccountInterface::class));
    }

    public function testShouldAllowGetHolderPreviouslySet(): void
    {
        $bankAccount = new BankAccount();
        $bankAccount->setHolder('theVal');

        $this->assertSame('theVal', $bankAccount->getHolder());
    }

    public function testShouldAllowGetNumberPreviouslySet(): void
    {
        $bankAccount = new BankAccount();
        $bankAccount->setNumber('theVal');

        $this->assertSame('theVal', $bankAccount->getNumber());
    }

    public function testShouldAllowGetBankCodePreviouslySet(): void
    {
        $bankAccount = new BankAccount();
        $bankAccount->setBankCode('theVal');

        $this->assertSame('theVal', $bankAccount->getBankCode());
    }

    public function testShouldAllowGetBankCountryCodePreviouslySet(): void
    {
        $bankAccount = new BankAccount();
        $bankAccount->setBankCountryCode('theVal');

        $this->assertSame('theVal', $bankAccount->getBankCountryCode());
    }

    public function testShouldAllowGetIbanPreviouslySet(): void
    {
        $bankAccount = new BankAccount();
        $bankAccount->setIban('theVal');

        $this->assertSame('theVal', $bankAccount->getIban());
    }

    public function testShouldAllowGetBicPreviouslySet(): void
    {
        $bankAccount = new BankAccount();
        $bankAccount->setBic('theVal');

        $this->assertSame('theVal', $bankAccount->getBic());
    }
}
