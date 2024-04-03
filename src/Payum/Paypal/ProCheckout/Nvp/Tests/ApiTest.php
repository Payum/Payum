<?php

namespace Payum\Paypal\ProCheckout\Nvp\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ProCheckout\Nvp\Api;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ApiTest extends TestCase
{
    protected function setUp(): void
    {
        Psr17FactoryDiscovery::prependStrategy(MockClientStrategy::class);
    }

    public function testThrowIfRequiredOptionsNotSetInConstructor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The username, password, partner, vendor fields are required.');
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());
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
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());
    }

    public function testShouldAddTRXTYPEOnDoSaleCall(): void
    {
        $api = new Api([
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

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
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

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
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

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
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

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
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertSame('https://payflowpro.paypal.com/', (string) $request->getUri());

                return Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody($request->getBody());
            })
        ;

        $api = new Api([
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $api->doCredit([]);
    }

    public function testShouldUseSandboxApiEndpointIfSandboxTrue(): void
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertSame('https://pilot-payflowpro.paypal.com/', (string) $request->getUri());

                return Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody($request->getBody());
            })
        ;

        $api = new Api([
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
            'sandbox' => true,
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $api->doCredit([]);
    }

    protected function createHttpClientMock(): MockObject | ClientInterface
    {
        return $this->createMock(ClientInterface::class);
    }

    protected function createHttpMessageFactory(): RequestFactoryInterface
    {
        return Psr17FactoryDiscovery::findRequestFactory();
    }

    protected function createHttpStreamFactory(): StreamFactoryInterface
    {
        return Psr17FactoryDiscovery::findStreamFactory();
    }

    protected function createSuccessHttpClientStub(): MockObject | ClientInterface
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->method('sendRequest')
            ->willReturnCallback(static fn (RequestInterface $request): ResponseInterface => Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody($request->getBody()));

        return $clientMock;
    }
}
