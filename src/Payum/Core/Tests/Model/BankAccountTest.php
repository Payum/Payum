<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\BankAccount;
use Payum\Core\Model\BankAccountInterface;
use PHPUnit\Framework\TestCase;

class BankAccountTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExtendBankAccountInterface()
    {
        $rc = new \ReflectionClass(BankAccount::class);

        $this->assertTrue($rc->implementsInterface(BankAccountInterface::class));
    }

    /**
     * @test
     */
    public function shouldAllowGetHolderPreviouslySet()
    {
        $bankAccount = new BankAccount();
        $bankAccount->setHolder('theVal');

        $this->assertSame('theVal', $bankAccount->getHolder());
    }

    /**
     * @test
     */
    public function shouldAllowGetNumberPreviouslySet()
    {
        $bankAccount = new BankAccount();
        $bankAccount->setNumber('theVal');

        $this->assertSame('theVal', $bankAccount->getNumber());
    }

    /**
     * @test
     */
    public function shouldAllowGetBankCodePreviouslySet()
    {
        $bankAccount = new BankAccount();
        $bankAccount->setBankCode('theVal');

        $this->assertSame('theVal', $bankAccount->getBankCode());
    }

    /**
     * @test
     */
    public function shouldAllowGetBankCountryCodePreviouslySet()
    {
        $bankAccount = new BankAccount();
        $bankAccount->setBankCountryCode('theVal');

        $this->assertSame('theVal', $bankAccount->getBankCountryCode());
    }

    /**
     * @test
     */
    public function shouldAllowGetIbanPreviouslySet()
    {
        $bankAccount = new BankAccount();
        $bankAccount->setIban('theVal');

        $this->assertSame('theVal', $bankAccount->getIban());
    }

    /**
     * @test
     */
    public function shouldAllowGetBicPreviouslySet()
    {
        $bankAccount = new BankAccount();
        $bankAccount->setBic('theVal');

        $this->assertSame('theVal', $bankAccount->getBic());
    }
}
