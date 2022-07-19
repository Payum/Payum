<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\ProHosted\Nvp\Api;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ApiTest extends TestCase
{
    public function testThrowIfSandboxOptionNotSetInConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory());
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
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

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
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

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
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doCreateButton([]);

        $this->assertArrayHasKey('VERSION', $result);
        $this->assertSame(Api::VERSION, $result['VERSION']);
    }

    public function testShouldUseRealApiEndpointIfSandboxFalse(): void
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock->expects($this->once())->method('send')->willReturnCallback(function (RequestInterface $request) use (
            $testCase
        ): Response {
            $testCase->assertSame('https://api-3t.paypal.com/nvp', (string) $request->getUri());

            return new Response(200, [], $request->getBody());
        });

        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
            'sandbox' => false,
            'return' => 'optionReturnUrl',
        ], $clientMock, $this->createHttpMessageFactory());

        $api->doCreateButton([]);
    }

    public function testShouldUseSandboxApiEndpointIfSandboxTrue(): void
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock->expects($this->once())->method('send')->willReturnCallback(function (RequestInterface $request) use (
            $testCase
        ): Response {
            $testCase->assertSame('https://api-3t.sandbox.paypal.com/nvp', (string) $request->getUri());

            return new Response(200, [], $request->getBody());
        });

        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'business' => 'a_business',
            'sandbox' => true,
            'return' => 'optionReturnUrl',
        ], $clientMock, $this->createHttpMessageFactory());

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
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

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
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $api->doCreateButton([]);
    }

    public function testThrowIfRequiredOptionsNotSetInConstructor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The username, password, signature fields are required.');
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory());
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
        $clientMock->method('send')->willReturnCallback(fn (RequestInterface $request) => new Response(200, [], $request->getBody()));

        return $clientMock;
    }
}
