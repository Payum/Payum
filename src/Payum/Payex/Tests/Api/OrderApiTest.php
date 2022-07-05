<?php

namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\SoapClientFactory;

class OrderApiTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseApi()
    {
        $rc = new \ReflectionClass(\Payum\Payex\Api\OrderApi::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Payex\Api\BaseApi::class));
    }

    public function testThrowIfAccountNumberOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        new OrderApi(new SoapClientFactory(), []);
    }

    public function testThrowIfEncryptionKeyOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
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
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
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
        $response = new \stdClass();
        $response->Initialize8Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Initialize8')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(\Payum\Payex\Api\SoapClientFactory::class, ['createWsdlClient']);
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

        $this->assertEquals(['fooValue'], $result);
    }

    public function testShouldUseSoapClientOnCompleteAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->CompleteResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Complete')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(\Payum\Payex\Api\SoapClientFactory::class, ['createWsdlClient']);
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

        $this->assertEquals(['fooValue'], $result);
    }

    public function testShouldUseSoapClientOnCheckAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->Check2Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Check2')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(\Payum\Payex\Api\SoapClientFactory::class, ['createWsdlClient']);
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

        $this->assertEquals(['fooValue'], $result);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SoapClient
     */
    private function createSoapClientMock()
    {
        return $this->createMock(OrderSoapClient::class);
    }
}

class OrderSoapClient extends \SoapClient
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
