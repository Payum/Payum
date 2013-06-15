<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Api\SoapClientFactory;

class AgreementApiTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApi()
    {
        $rc = new \ReflectionClass('Payum\Payex\Api\AgreementApi');
        
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
        new AgreementApi(new SoapClientFactory, array());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The encryptionKey option must be set.
     */
    public function throwIfEncryptionKeyOptionNotSet()
    {
        new AgreementApi(
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
        new AgreementApi(
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
        new AgreementApi(
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
    public function shouldUseSoapClientOnCreateAgreementAndConvertItsResponse()
    {
        $response = new \stdClass;
        $response->CreateAgreement3Result = '<foo>fooValue</foo>';
        
        $soapClientMock = $this->getMock('SoapClient', array('CreateAgreement3'), array(), '', false);
        $soapClientMock
            ->expects($this->once())
            ->method('CreateAgreement3')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
        ;
        
        $clientFactoryMock = $this->getMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->will($this->returnValue($soapClientMock))
        ;

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            array(
                'encryptionKey' => 'aKey',
                'accountNumber' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $agreementApi->create(array());
        
        $this->assertEquals(array('fooValue'),  $result);
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnCheckAgreementAndConvertItsResponse()
    {
        $response = new \stdClass;
        $response->CheckResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->getMock('SoapClient', array('Check'), array(), '', false);
        $soapClientMock
            ->expects($this->once())
            ->method('Check')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
        ;

        $clientFactoryMock = $this->getMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->will($this->returnValue($soapClientMock))
        ;

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            array(
                'encryptionKey' => 'aKey',
                'accountNumber' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $agreementApi->check(array());

        $this->assertEquals(array('fooValue'),  $result);
    }
}