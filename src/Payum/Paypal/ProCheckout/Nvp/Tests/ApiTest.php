<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Psr\Http\Message\RequestInterface;

class ApiTest extends \PHPUnit\Framework\TestCase
{
    public function testThrowIfRequiredOptionsNotSetInConstructor()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The username, password, partner, vendor fields are required.');
        new Api(array(), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testThrowIfSandboxOptionsNotBooleanInConstructor()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api(array(
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender',
            'sandbox' => 'notABool'
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testShouldAddTRXTYPEOnDoSaleCall()
    {
        $api = new Api(array(
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender'
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doSale(array());

        $this->assertArrayHasKey('TRXTYPE', $result);
        $this->assertSame(Api::TRXTYPE_SALE, $result['TRXTYPE']);
    }

    public function testShouldAddTRXTYPEOnDoCreditCall()
    {
        $api = new Api(array(
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender'
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doCredit(array());

        $this->assertArrayHasKey('TRXTYPE', $result);
        $this->assertSame(Api::TRXTYPE_CREDIT, $result['TRXTYPE']);
    }

    public function testShouldAddAuthorizeFieldsOnDoSaleCall()
    {
        $api = new Api(array(
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender'
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doSale(array());

        $this->assertArrayHasKey('USER', $result);
        $this->assertSame('theUsername', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertSame('thePassword', $result['PWD']);

        $this->assertArrayHasKey('PARTNER', $result);
        $this->assertSame('thePartner', $result['PARTNER']);

        $this->assertArrayHasKey('VENDOR', $result);
        $this->assertSame('theVendor', $result['VENDOR']);

        $this->assertArrayHasKey('TENDER', $result);
        $this->assertSame('theTender', $result['TENDER']);
    }

    public function testShouldAddAuthorizeFieldsOnDoCreditCall()
    {
        $api = new Api(array(
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender'
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doCredit(array());

        $this->assertArrayHasKey('USER', $result);
        $this->assertSame('theUsername', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertSame('thePassword', $result['PWD']);

        $this->assertArrayHasKey('PARTNER', $result);
        $this->assertSame('thePartner', $result['PARTNER']);

        $this->assertArrayHasKey('VENDOR', $result);
        $this->assertSame('theVendor', $result['VENDOR']);

        $this->assertArrayHasKey('TENDER', $result);
        $this->assertSame('theTender', $result['TENDER']);
    }

    public function testShouldUseRealApiEndpointIfSandboxFalse()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertSame('https://payflowpro.paypal.com/', (string) $request->getUri());

                return new Response(200, [], $request->getBody());
            })
        ;

        $api = new Api(array(
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $api->doCredit(array());
    }

    public function testShouldUseSandboxApiEndpointIfSandboxTrue()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertSame('https://pilot-payflowpro.paypal.com/', (string) $request->getUri());

                return new Response(200, [], $request->getBody());
            })
        ;

        $api = new Api(array(
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
            'sandbox' => true,
        ), $clientMock, $this->createHttpMessageFactory());

        $api->doCredit(array());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->createMock('Payum\Core\HttpClientInterface');
    }

    /**
     * @return \Http\Message\MessageFactory
     */
    protected function createHttpMessageFactory()
    {
        return new GuzzleMessageFactory();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createSuccessHttpClientStub()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(200, [], $request->getBody());
            })
        ;

        return $clientMock;
    }
}
