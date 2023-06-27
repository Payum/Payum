<?php

namespace Payum\Paypal\ProCheckout\Nvp\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\Exception\LogicException;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\ProCheckout\Nvp\Api;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ApiTest extends TestCase
{
    public function testThrowIfRequiredOptionsNotSetInConstructor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The username, password, partner, vendor fields are required.');
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testThrowIfSandboxOptionsNotBooleanInConstructor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api([
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender',
            'sandbox' => 'notABool',
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testShouldAddTRXTYPEOnDoSaleCall(): void
    {
        $api = new Api([
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doSale([]);

        $this->assertArrayHasKey('TRXTYPE', $result);
        $this->assertSame(Api::TRXTYPE_SALE, $result['TRXTYPE']);
    }

    public function testShouldAddTRXTYPEOnDoCreditCall(): void
    {
        $api = new Api([
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doCredit([]);

        $this->assertArrayHasKey('TRXTYPE', $result);
        $this->assertSame(Api::TRXTYPE_CREDIT, $result['TRXTYPE']);
    }

    public function testShouldAddAuthorizeFieldsOnDoSaleCall(): void
    {
        $api = new Api([
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doSale([]);

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

    public function testShouldAddAuthorizeFieldsOnDoCreditCall(): void
    {
        $api = new Api([
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doCredit([]);

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

    public function testShouldUseRealApiEndpointIfSandboxFalse(): void
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase): Response {
                $testCase->assertSame('https://payflowpro.paypal.com/', (string) $request->getUri());

                return new Response(200, [], $request->getBody());
            })
        ;

        $api = new Api([
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory());

        $api->doCredit([]);
    }

    public function testShouldUseSandboxApiEndpointIfSandboxTrue(): void
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase): Response {
                $testCase->assertSame('https://pilot-payflowpro.paypal.com/', (string) $request->getUri());

                return new Response(200, [], $request->getBody());
            })
        ;

        $api = new Api([
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
            'sandbox' => true,
        ], $clientMock, $this->createHttpMessageFactory());

        $api->doCredit([]);
    }

    /**
     * @return MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->createMock(HttpClientInterface::class);
    }

    protected function createHttpMessageFactory(): GuzzleMessageFactory
    {
        return new GuzzleMessageFactory();
    }

    /**
     * @return MockObject|HttpClientInterface
     */
    protected function createSuccessHttpClientStub()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->method('send')
            ->willReturnCallback(fn (RequestInterface $request) => new Response(200, [], $request->getBody()))
        ;

        return $clientMock;
    }
}
