<?php

namespace Payum\Paypal\Masspay\Nvp\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\Masspay\Nvp\Api;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ApiTest extends TestCase
{
    public function testThrowIfRequiredOptionsNotSetInConstructor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The username, password, signature fields are required.');
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());
    }

    public function testThrowIfSandboxOptionNotSetInConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());
    }

    public function testShouldAddMethodOnMasspayCall(): void
    {
        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $result = $api->massPay([]);

        $this->assertArrayHasKey('METHOD', $result);
        $this->assertSame('MassPay', $result['METHOD']);
    }

    public function testShouldAddAuthorizeFieldsOnMasspayCall(): void
    {
        $api = new Api([
            'username' => 'the_username',
            'password' => 'the_password',
            'signature' => 'the_signature',
            'sandbox' => true,
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $result = $api->massPay([]);

        $this->assertArrayHasKey('USER', $result);
        $this->assertSame('the_username', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertSame('the_password', $result['PWD']);

        $this->assertArrayHasKey('SIGNATURE', $result);
        $this->assertSame('the_signature', $result['SIGNATURE']);
    }

    public function testShouldAddVersionOnMasspayCall(): void
    {
        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $result = $api->massPay([]);

        $this->assertArrayHasKey('VERSION', $result);
        $this->assertSame(Api::VERSION, $result['VERSION']);
    }

    public function testShouldUseRealApiEndpointIfSandboxFalse(): void
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) {
                $this->assertSame('https://api-3t.paypal.com/nvp', (string) $request->getUri());

                return Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody($request->getBody());
            })
        ;

        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $api->massPay([]);
    }

    public function testShouldUseSandboxApiEndpointIfSandboxTrue(): void
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) {
                $this->assertSame('https://api-3t.sandbox.paypal.com/nvp', (string) $request->getUri());

                return Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody($request->getBody());
            })
        ;

        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $api->massPay([]);
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
            ->willReturnCallback(fn (RequestInterface $request) => Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody($request->getBody()))
        ;

        return $clientMock;
    }
}
