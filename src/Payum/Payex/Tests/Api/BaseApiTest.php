<?php

namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\SoapClientFactory;

class BaseApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Payex\Api\BaseApi');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function throwIfAccountNumberOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        $this->getMockForAbstractClass('Payum\Payex\Api\BaseApi', array(
            new SoapClientFactory(),
            array(),
        ));
    }

    /**
     * @test
     */
    public function throwIfEncryptionKeyOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The encryption_key option must be set.');
        $this->getMockForAbstractClass('Payum\Payex\Api\BaseApi', array(
            new SoapClientFactory(),
            array(
                'account_number' => 'aNumber',
            ),
        ));
    }

    /**
     * @test
     */
    public function throwIfNotBoolSandboxOptionGiven()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        $this->getMockForAbstractClass('Payum\Payex\Api\BaseApi', array(
            new SoapClientFactory(),
            array(
                'account_number' => 'aNumber',
                'encryption_key' => 'aKey',
                'sandbox' => 'not a bool',
            ),
        ));
    }
}
