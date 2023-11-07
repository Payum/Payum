<?php

namespace Payum\Payex\Tests\Api;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Api\BaseApi;
use Payum\Payex\Api\SoapClientFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SoapClient;
use stdClass;

class AgreementApiTest extends TestCase
{
    public function testShouldBeSubClassOfBaseApi()
    {
        $rc = new ReflectionClass(AgreementApi::class);

        $this->assertTrue($rc->isSubclassOf(BaseApi::class));
    }

    public function testThrowIfAccountNumberOptionNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The account_number option must be set.');
        new AgreementApi(new SoapClientFactory(), []);
    }

    public function testThrowIfEncryptionKeyOptionNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The encryption_key option must be set.');
        new AgreementApi(
            new SoapClientFactory(),
            [
                'account_number' => 'aNumber',
            ]
        );
    }

    public function testThrowIfNotBoolSandboxOptionGiven()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new AgreementApi(
            new SoapClientFactory(),
            [
                'account_number' => 'aNumber',
                'encryption_key' => 'aKey',
                'sandbox' => 'not a bool',
            ]
        );
    }

    public function testShouldUseSoapClientOnCreateAgreementAndConvertItsResponse()
    {
        $response = new stdClass();
        $response->CreateAgreement3Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('CreateAgreement3')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(SoapClientFactory::class);
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $agreementApi->create([]);

        $this->assertSame(['fooValue'], $result);
    }

    public function testShouldUseSoapClientOnCheckAgreementAndConvertItsResponse()
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

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $agreementApi->check([]);

        $this->assertSame(['fooValue'], $result);
    }

    public function testShouldUseSoapClientOnDeleteAgreementAndConvertItsResponse()
    {
        $response = new stdClass();
        $response->DeleteAgreementResult = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('DeleteAgreement')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(SoapClientFactory::class);
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $agreementApi->delete([]);

        $this->assertSame(['fooValue'], $result);
    }

    public function testShouldUseSoapClientOnAgreementAutoPayAndConvertItsResponse()
    {
        $response = new stdClass();
        $response->AutoPay3Result = '<foo>fooValue</foo>';

        $soapClientMock = $this->createSoapClientMock();
        $soapClientMock
            ->expects($this->once())
            ->method('AutoPay3')
            ->with($this->isType('array'))
            ->willReturn($response)
        ;

        $clientFactoryMock = $this->createMock(SoapClientFactory::class);
        $clientFactoryMock
            ->expects($this->atLeastOnce())
            ->method('createWsdlClient')
            ->willReturn($soapClientMock)
        ;

        $agreementApi = new AgreementApi(
            $clientFactoryMock,
            [
                'encryption_key' => 'aKey',
                'account_number' => 'aNumber',
                'sandbox' => true,
            ]
        );

        $result = $agreementApi->autoPay([]);

        $this->assertSame(['fooValue'], $result);
    }

    /**
     * @return MockObject|SoapClient
     */
    private function createSoapClientMock()
    {
        return $this->createMock(AgreementSoapClient::class);
    }
}

class AgreementSoapClient extends SoapClient
{
    public function __construct()
    {
    }

    public function CreateAgreement3()
    {
    }

    public function Check()
    {
    }

    public function DeleteAgreement()
    {
    }

    public function AutoPay3()
    {
    }
}
