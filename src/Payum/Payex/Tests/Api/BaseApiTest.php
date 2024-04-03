<?php

namespace Payum\Payex\Tests\Api;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Payex\Api\BaseApi;
use Payum\Payex\Api\SoapClientFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BaseApiTest extends TestCase
{
    public function testShouldBeAbstract(): void
    {
        $rc = new ReflectionClass(BaseApi::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testThrowIfAccountNumberOptionNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        $this->getMockForAbstractClass(BaseApi::class, [
            new SoapClientFactory(),
            [],
        ]);
    }

    public function testThrowIfEncryptionKeyOptionNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The encryption_key option must be set.');
        $this->getMockForAbstractClass(BaseApi::class, [
            new SoapClientFactory(),
            [
                'account_number' => 'aNumber',
            ],
        ]);
    }

    public function testThrowIfNotBoolSandboxOptionGiven(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        $this->getMockForAbstractClass(BaseApi::class, [
            new SoapClientFactory(),
            [
                'account_number' => 'aNumber',
                'encryption_key' => 'aKey',
                'sandbox' => 'not a bool',
            ],
        ]);
    }
}
