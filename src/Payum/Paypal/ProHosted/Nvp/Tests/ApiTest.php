<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RuntimeException;
use Payum\Paypal\ProHosted\Nvp\Api;
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
        Psr18ClientDiscovery::prependStrategy(MockClientStrategy::class);
    }

    public function testThrowIfSandboxOptionNotSetInConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());
    }

    public function testShouldUseReturnUrlSetInFormRequest(): void
    {
        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
            'sandbox' => true,
            'return' => 'optionReturnUrl',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $result = $api->doCreateButton([
            'return' => 'formRequestReturnUrl',
        ]);

        $this->assertContains('return=formRequestReturnUrl', $result);
    }

    public function testShouldAddAuthorizeFieldsOnDoCreateButtonCall(): void
    {
        $api = new Api([
            'username' => 'the_username',
            'password' => 'the_password',
            'signature' => 'the_signature',
            'business' => 'the_business',
            'subject' => 'the_business',
            'sandbox' => true,
            'return' => 'optionReturnUrl',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $result = $api->doCreateButton([]);

        $this->assertArrayHasKey('USER', $result);
        $this->assertSame('the_username', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertSame('the_password', $result['PWD']);

        $this->assertArrayHasKey('SIGNATURE', $result);
        $this->assertSame('the_signature', $result['SIGNATURE']);

        $this->assertArrayHasKey('BUSINESS', $result);
        $this->assertSame('the_business', $result['BUSINESS']);

        $this->assertArrayHasKey('SUBJECT', $result);
        $this->assertSame('the_business', $result['SUBJECT']);
    }

    public function testShouldAddVersionOnDoCreateButtonCall(): void
    {
        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
            'sandbox' => true,
            'return' => 'optionReturnUrl',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $result = $api->doCreateButton([]);

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
            });

        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
            'sandbox' => false,
            'return' => 'optionReturnUrl',
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $api->doCreateButton([]);
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
            });

        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
            'sandbox' => true,
            'return' => 'optionReturnUrl',
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $api->doCreateButton([]);
    }

    public function testShouldAddMethodOnDoCreateButtonCall(): void
    {
        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
            'sandbox' => true,
            'return' => 'optionReturnUrl',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $result = $api->doCreateButton([]);

        $this->assertArrayHasKey('METHOD', $result);
        $this->assertSame('BMCreateButton', $result['METHOD']);
    }

    public function testThrowIfReturnUrlNeitherSetToFormRequestNorToOptions(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The return must be set either to FormRequest or to options.');
        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $api->doCreateButton([]);
    }

    public function testThrowIfRequiredOptionsNotSetInConstructor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The username, password, signature fields are required.');
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());
    }

    protected function createHttpClientMock(): MockObject | ClientInterface
    {
        return $this->createMock(ClientInterface::class);
    }

    protected function createHttpMessageFactory(): RequestFactoryInterface
    {
        return Psr17FactoryDiscovery::findRequestFactory();
    }

    protected function createSuccessHttpClientStub(): MockObject | ClientInterface
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->method('sendRequest')
            ->willReturnCallback(static fn (RequestInterface $request): ResponseInterface => Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody($request->getBody()));

        return $clientMock;
    }

    private function createHttpStreamFactory(): StreamFactoryInterface
    {
        return Psr17FactoryDiscovery::findStreamFactory();
    }
}
