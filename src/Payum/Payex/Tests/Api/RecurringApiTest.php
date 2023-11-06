<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Api\SoapClientFactory;

class RecurringApiTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseApi()
    {
        $rc = new \ReflectionClass('Payum\Payex\Api\RecurringApi');

        $this->assertTrue($rc->isSubclassOf('Payum\Payex\Api\BaseApi'));
    }

    public function testThrowIfAccountNumberOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        new RecurringApi(new SoapClientFactory(), array());
    }

    public function testThrowIfEncryptionKeyOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The encryption_key option must be set.');
        new RecurringApi(
            new SoapClientFactory(),
            array(
                'account_number' => 'aNumber',
            )
        );
    }

    public function testThrowIfNotBoolSandboxOptionGiven()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new RecurringApi(
            new SoapClientFactory(),
            array(
                'account_number' => 'aNumber',
                'encryption_key' => 'aKey',
                'sandbox' => 'not a bool',
            )
        );
    }

    public function testShouldUseSoapClientOnStartRecurringPaymentAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->StartResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Start')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $recurringApi = new RecurringApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $recurringApi->start(array());

        $this->assertSame(array('fooValue'), $result);
    }

    public function testShouldUseSoapClientOnStopRecurringPaymentAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->StopResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Stop')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $recurringApi = new RecurringApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $recurringApi->stop(array());

        $this->assertSame(array('fooValue'), $result);
    }

    public function testShouldUseSoapClientOnCheckRecurringPaymentAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->CheckResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Check')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $recurringApi = new RecurringApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $recurringApi->check(array());

        $this->assertSame(array('fooValue'), $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\SoapClient
     */
    private function createSoapClientMock()
    {
        return $this->createMock(RecurringSoapClient::class);
    }
}

class RecurringSoapClient extends \SoapClient {
    public function __construct() {}

    public function Start() {}
    public function Stop() {}
    public function Check() {}
};
