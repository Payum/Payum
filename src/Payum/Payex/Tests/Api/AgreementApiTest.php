<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Api\SoapClientFactory;

class AgreementApiTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseApi()
    {
        $rc = new \ReflectionClass('Payum\Payex\Api\AgreementApi');

        $this->assertTrue($rc->isSubclassOf('Payum\Payex\Api\BaseApi'));
    }

    public function testThrowIfAccountNumberOptionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        new AgreementApi(new SoapClientFactory(), array());
    }

    public function testThrowIfEncryptionKeyOptionNotSet()
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

    public function testThrowIfNotBoolSandboxOptionGiven()
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

    public function testShouldUseSoapClientOnCreateAgreementAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->CreateAgreement3Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('CreateAgreement3')
            ->with($this->isType('array'))
            ->willReturn($response)
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

        $this->assertSame(array('fooValue'), $result);
    }

    public function testShouldUseSoapClientOnCheckAgreementAndConvertItsResponse()
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

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            array(
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            )
        );

        $result = $agreementApi->check(array());

        $this->assertSame(array('fooValue'), $result);
    }

    public function testShouldUseSoapClientOnDeleteAgreementAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->DeleteAgreementResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('DeleteAgreement')
            ->with($this->isType('array'))
            ->willReturn($response)
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

        $this->assertSame(array('fooValue'), $result);
    }

    public function testShouldUseSoapClientOnAgreementAutoPayAndConvertItsResponse()
    {
        $response = new \stdClass();
        $response->AutoPay3Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('AutoPay3')
            ->with($this->isType('array'))
            ->willReturn($response)
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

        $this->assertSame(array('fooValue'), $result);
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
