<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Api\SoapClientFactory;

class RecurringApiTest extends \PHPUnit_Framework_TestCase 
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
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The accountNumber option must be set.
     */
    public function throwIfAccountNumberOptionNotSet()
    {
        new RecurringApi(new SoapClientFactory, array());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The encryptionKey option must be set.
     */
    public function throwIfEncryptionKeyOptionNotSet()
    {
        new RecurringApi(
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
        new RecurringApi(
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
        new RecurringApi(
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
    public function shouldUseSoapClientOnStartAgreementAndConvertItsResponse()
    {
        $response = new \stdClass;
        $response->StartResult = '<foo>fooValue</foo>';
        
        $soapClientMock = $this->getMock('SoapClient', array('Start'), array(), '', false);
        $soapClientMock
            ->expects($this->once())
            ->method('Start')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
        ;
        
        $clientFactoryMock = $this->getMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->will($this->returnValue($soapClientMock))
        ;

        $recurringApi = new RecurringApi(
            $clientFactoryMock,
            array(
                'encryptionKey' => 'aKey',
                'accountNumber' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $recurringApi->start(array());
        
        $this->assertEquals(array('fooValue'),  $result);
    }
}