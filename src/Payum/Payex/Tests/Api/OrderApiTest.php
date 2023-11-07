<?php

namespace Payum\Payex\Tests\Api;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Payex\Api\BaseApi;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\SoapClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SoapClient;
use stdClass;

class OrderApiTest extends TestCase
{
    public function testShouldBeSubClassOfBaseApi()
    {
        $rc = new ReflectionClass(OrderApi::class);

        $this->assertTrue($rc->isSubclassOf(BaseApi::class));
    }

    public function testThrowIfAccountNumberOptionNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        new OrderApi(new SoapClientFactory(), []);
    }

    public function testThrowIfEncryptionKeyOptionNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The encryption_key option must be set.');
        new OrderApi(
            new SoapClientFactory(),
            [
                'account_number' => 'aNumber',
            ]
        );
    }

    public function testThrowIfNotBoolSandboxOptionGiven()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new OrderApi(
            new SoapClientFactory(),
            [
                'account_number' => 'aNumber',
                'encryption_key' => 'aKey',
                'sandbox' => 'not a bool',
            ]
        );
    }

    public function testShouldUseSoapClientOnInitialize8AndConvertItsResponse()
    {
        $response = new stdClass();
        $response->Initialize8Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Initialize8')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(SoapClientFactory::class);
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $orderApi = new OrderApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $orderApi->initialize([]);

        $this->assertSame(['fooValue'], $result);
    }

    public function testShouldUseSoapClientOnCompleteAndConvertItsResponse()
    {
        $response = new stdClass();
        $response->CompleteResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Complete')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(SoapClientFactory::class);
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $orderApi = new OrderApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $orderApi->complete([]);

        $this->assertSame(['fooValue'], $result);
    }

    public function testShouldUseSoapClientOnCheckAndConvertItsResponse()
    {
        $response = new stdClass();
        $response->Check2Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Check2')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(SoapClientFactory::class);
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $orderApi = new OrderApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $orderApi->check([]);

        $this->assertSame(['fooValue'], $result);
    }

    /**
     * @return MockObject|SoapClient
     */
    private function createSoapClientMock()
    {
        return $this->createMock(OrderSoapClient::class);
    }
}

class OrderSoapClient extends SoapClient
{
    public function __construct()
    {
    }

    public function Initialize8()
    {
    }

    public function Complete()
    {
    }

    public function Check2()
    {
    }
}
