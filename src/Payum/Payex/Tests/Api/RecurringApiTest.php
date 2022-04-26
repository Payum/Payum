<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Api\SoapClientFactory;

class RecurringApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApi()
    {
        $rc = new \ReflectionClass('Payum\Payex\Api\RecurringApi');

        $this->assertTrue($rc->isSubclassOf('Payum\Payex\Api\BaseApi'));
    }

    /**
     * @test
     */
    public function throwIfAccountNumberOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        new RecurringApi(new SoapClientFactory(), array());
    }

    /**
     * @test
     */
    public function throwIfEncryptionKeyOptionNotSet()
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

    /**
     * @test
     */
    public function throwIfNotBoolSandboxOptionGiven()
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

    /**
     * @test
     */
    public function shouldUseSoapClientOnStartRecurringPaymentAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->StartResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Start')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
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

        $this->assertEquals(array('fooValue'), $result);
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnStopRecurringPaymentAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->StopResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Stop')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
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

        $this->assertEquals(array('fooValue'), $result);
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnCheckRecurringPaymentAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->CheckResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('Check')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
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

        $this->assertEquals(array('fooValue'), $result);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\SoapClient
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
