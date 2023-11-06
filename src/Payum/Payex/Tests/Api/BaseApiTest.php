<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\SoapClientFactory;

class BaseApiTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Payex\Api\BaseApi');

        $this->assertTrue($rc->isAbstract());
    }

    public function testThrowIfAccountNumberOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        $this->getMockForAbstractClass('Payum\Payex\Api\BaseApi', array(
            new SoapClientFactory(),
            array(),
        ));
    }

    public function testThrowIfEncryptionKeyOptionNotSet()
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

    public function testThrowIfNotBoolSandboxOptionGiven()
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
