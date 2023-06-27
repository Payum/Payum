<?php

namespace Payum\Payex\Tests\Api;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Payex\Api\BaseApi;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Api\SoapClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SoapClient;
use stdClass;

class RecurringApiTest extends TestCase
{
    public function testShouldBeSubClassOfBaseApi(): void
    {
        $rc = new ReflectionClass(RecurringApi::class);

        $this->assertTrue($rc->isSubclassOf(BaseApi::class));
    }

    public function testThrowIfAccountNumberOptionNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        new RecurringApi(new SoapClientFactory(), []);
    }

    public function testThrowIfEncryptionKeyOptionNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The encryption_key option must be set.');
        new RecurringApi(
            new SoapClientFactory(),
            [
                'account_number' => 'aNumber',
            ]
        );
    }

    public function testThrowIfNotBoolSandboxOptionGiven(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new RecurringApi(
            new SoapClientFactory(),
            [
                'account_number' => 'aNumber',
                'encryption_key' => 'aKey',
                'sandbox' => 'not a bool',
            ]
        );
    }

    public function testShouldUseSoapClientOnStartRecurringPaymentAndConvertItsResponse(): void
    {
        $response = new stdClass();
        $response->StartResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Start')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(SoapClientFactory::class);
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $recurringApi = new RecurringApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $recurringApi->start([]);

        $this->assertEquals(['fooValue'], $result);
    }

    public function testShouldUseSoapClientOnStopRecurringPaymentAndConvertItsResponse(): void
    {
        $response = new stdClass();
        $response->StopResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Stop')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(SoapClientFactory::class);
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $recurringApi = new RecurringApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $recurringApi->stop([]);

        $this->assertEquals(['fooValue'], $result);
    }

    public function testShouldUseSoapClientOnCheckRecurringPaymentAndConvertItsResponse(): void
    {
        $response = new stdClass();
        $response->CheckResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Check')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(SoapClientFactory::class);
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $recurringApi = new RecurringApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $recurringApi->check([]);

        $this->assertEquals(['fooValue'], $result);
    }

    /**
     * @return MockObject|SoapClient
     */
    private function createSoapClientMock()
    {
        return $this->createMock(RecurringSoapClient::class);
    }
}

class RecurringSoapClient extends SoapClient
{
    public function __construct()
    {
    }

    public function Start(): void
    {
    }

    public function Stop(): void
    {
    }

    public function Check(): void
    {
    }
}
