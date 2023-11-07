<?php

namespace Payum\Paypal\Masspay\Nvp\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\Masspay\Nvp\Api;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ApiTest extends TestCase
{
    public function testThrowIfRequiredOptionsNotSetInConstructor()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The username, password, signature fields are required.');
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testThrowIfSandboxOptionNotSetInConstructor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testShouldAddMethodOnMasspayCall()
    {
        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->massPay([]);

        $this->assertArrayHasKey('METHOD', $result);
        $this->assertSame('MassPay', $result['METHOD']);
    }

    public function testShouldAddAuthorizeFieldsOnMasspayCall()
    {
        $api = new Api([
            'username' => 'the_username',
            'password' => 'the_password',
            'signature' => 'the_signature',
            'sandbox' => true,
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->massPay([]);

        $this->assertArrayHasKey('USER', $result);
        $this->assertSame('the_username', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertSame('the_password', $result['PWD']);

        $this->assertArrayHasKey('SIGNATURE', $result);
        $this->assertSame('the_signature', $result['SIGNATURE']);
    }

    public function testShouldAddVersionOnMasspayCall()
    {
        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ], $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->massPay([]);

        $this->assertArrayHasKey('VERSION', $result);
        $this->assertSame(Api::VERSION, $result['VERSION']);
    }

    public function testShouldUseRealApiEndpointIfSandboxFalse()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertSame('https://api-3t.paypal.com/nvp', (string) $request->getUri());

                return new Response(200, [], $request->getBody());
            })
        ;

        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory());

        $api->massPay([]);
    }

    public function testShouldUseSandboxApiEndpointIfSandboxTrue()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertSame('https://api-3t.sandbox.paypal.com/nvp', (string) $request->getUri());

                return new Response(200, [], $request->getBody());
            })
        ;

        $api = new Api([
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ], $clientMock, $this->createHttpMessageFactory());

        $api->massPay([]);
    }

    /**
     * @return MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->createMock(HttpClientInterface::class);
    }

    /**
     * @return MessageFactory
     */
    protected function createHttpMessageFactory()
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
