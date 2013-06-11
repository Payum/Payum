<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\PxOrder;
use Payum\Payex\Api\SoapClientFactory;

class PxOrderTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test 
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The accountNumber option must be set.
     */
    public function throwIfAccountNumberOptionNotSet()
    {
        new PxOrder(new SoapClientFactory, array());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The encryptionKey option must be set.
     */
    public function throwIfEncryptionKeyOptionNotSet()
    {
        new PxOrder(
            new SoapClientFactory,
            array(
                'accountNumber' => 'aNumber',
            )
        );
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The boolean sandbox option must be set.
     */
    public function throwIfNotBoolSandboxOptionGiven()
    {
        new PxOrder(
            new SoapClientFactory,
            array(
                'accountNumber' => 'aNumber',
                'encryptionKey' => 'aKey',
                'sandbox' => 'not a bool',
            )
        );
    }

    /**
     * @test
     */
    public function couldBeConstructedWithValidOptions()
    {
        new PxOrder(
            new SoapClientFactory,
            array(
                'encryptionKey' => 'aKey',
                'accountNumber' => 'aNumber',
                'sandbox' => true,
            )
        );
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnInitialize8AndConvertItsResponse()
    {
        $response = new \stdClass;
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

        $pxOrder = new PxOrder(
            $clientFactoryMock,
            array(
                'encryptionKey' => 'aKey',
                'accountNumber' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $pxOrder->Initialize8(array());
        
        $this->assertEquals(array('fooValue'),  $result);
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnCompleteAndConvertItsResponse()
    {
        $response = new \stdClass;
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

        $pxOrder = new PxOrder(
            $clientFactoryMock,
            array(
                'encryptionKey' => 'aKey',
                'accountNumber' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $pxOrder->Complete(array());

        $this->assertEquals(array('fooValue'),  $result);
    }
}