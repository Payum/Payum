<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Api\SoapClientFactory;

class AgreementApiTest extends \PHPUnit\Framework\TestCase
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
     */
    public function throwIfAccountNumberOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        new AgreementApi(new SoapClientFactory(), array());
    }

    /**
     * @test
     */
    public function throwIfEncryptionKeyOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The encryption_key option must be set.');
        new AgreementApi(
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
        new AgreementApi(
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
    public function shouldUseSoapClientOnCreateAgreementAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->CreateAgreement3Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('CreateAgreement3')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
        ;

        $clientFactoryMock = $this->createMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $agreementApi->create(array());

        $this->assertEquals(array('fooValue'), $result);
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnCheckAgreementAndConvertItsResponse()
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

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $agreementApi->check(array());

        $this->assertEquals(array('fooValue'), $result);
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnDeleteAgreementAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->DeleteAgreementResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('DeleteAgreement')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
        ;

        $clientFactoryMock = $this->createMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $agreementApi->delete(array());

        $this->assertEquals(array('fooValue'), $result);
    }

    /**
     * @test
     */
    public function shouldUseSoapClientOnAgreementAutoPayAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->AutoPay3Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('AutoPay3')
            ->with($this->isType('array'))
            ->will($this->returnValue($response))
        ;

        $clientFactoryMock = $this->createMock('Payum\Payex\Api\SoapClientFactory', array('createWsdlClient'));
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $agreementApi->autoPay(array());

        $this->assertEquals(array('fooValue'), $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\SoapClient
     */
    private function createSoapClientMock()
    {
        return $this->createMock(AgreementSoapClient::class);
    }
}

class AgreementSoapClient extends \SoapClient {
    public function __construct() {}

    public function CreateAgreement3() {}
    public function Check() {}
    public function DeleteAgreement() {}
    public function AutoPay3() {}
};
