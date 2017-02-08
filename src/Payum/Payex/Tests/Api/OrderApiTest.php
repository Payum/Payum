<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\SoapClientFactory;

class OrderApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApi()
    {
        $rc = new \ReflectionClass('Payum\Payex\Api\OrderApi');

        $this->assertTrue($rc->isSubclassOf('Payum\Payex\Api\BaseApi'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The account_number option must be set.
     */
    public function throwIfAccountNumberOptionNotSet()
    {
        new OrderApi(new SoapClientFactory(), array());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The encryption_key option must be set.
     */
    public function throwIfEncryptionKeyOptionNotSet()
    {
        new OrderApi(
            new SoapClientFactory(),
            array(
                'account_number' => 'aNumber',
            )
        );
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The boolean sandbox option must be set.
     */
    public function throwIfNotBoolSandboxOptionGiven()
    {
        new OrderApi(
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
    public function couldBeConstructedWithValidOptions()
    {
        new OrderApi(
            new SoapClientFactory(),
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnInitialize8AndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->Initialize8Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->getMock('SoapClient', array('Initialize8'), array(), '', false);
        $soapClientMock
            ->expects($this->once())
            ->method('Initialize8')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
        ;

        $clientFactoryMock = $this->getMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->will($this->returnValue($soapClientMock))
        ;

        $orderApi = new OrderApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $orderApi->initialize(array());

        $this->assertEquals(array('fooValue'), $result);
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnCompleteAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->CompleteResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->getMock('SoapClient', array('Complete'), array(), '', false);
        $soapClientMock
            ->expects($this->once())
            ->method('Complete')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
        ;

        $clientFactoryMock = $this->getMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->will($this->returnValue($soapClientMock))
        ;

        $orderApi = new OrderApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $orderApi->complete(array());

        $this->assertEquals(array('fooValue'), $result);
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnCheckAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->Check2Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->getMock('SoapClient', array('Check2'), array(), '', false);
        $soapClientMock
            ->expects($this->once())
            ->method('Check2')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
        ;

        $clientFactoryMock = $this->getMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->will($this->returnValue($soapClientMock))
        ;

        $orderApi = new OrderApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $orderApi->check(array());

        $this->assertEquals(array('fooValue'), $result);
    }
}
