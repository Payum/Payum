<?php

namespace Payum\Core\Tests;

use Exception;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class PayumTest extends TestCase
{
    private RegistryInterface | MockObject $registryMock;
    private HttpRequestVerifierInterface | MockObject $httpRequestVerifierMock;
    private GenericTokenFactoryInterface | MockObject $tokenFactoryMock;
    private StorageInterface | MockObject $storageMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(RegistryInterface::class);
        $this->httpRequestVerifierMock = $this->createMock(HttpRequestVerifierInterface::class);
        $this->tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $this->storageMock = $this->createMock(StorageInterface::class);
    }

    public function testShouldImplementRegistryInterface(): void
    {
        $rc = new ReflectionClass(Payum::class);

        $this->assertTrue($rc->implementsInterface(RegistryInterface::class));
    }

    public function testShouldAllowGetHttpRequestVerifierSetInConstructor(): void
    {
        $payum = new Payum(
            $this->registryMock,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock
        );

        $this->assertSame($this->httpRequestVerifierMock, $payum->getHttpRequestVerifier());
    }

    public function testShouldAllowGetGenericTokenFactorySetInConstructor(): void
    {
        $payum = new Payum(
            $this->registryMock,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock
        );

        $this->assertSame($this->tokenFactoryMock, $payum->getTokenFactory());
    }

    public function testShouldAllowGetTokenStorageSetInConstructor(): void
    {
        $payum = new Payum(
            $this->registryMock,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock,
        );

        $this->assertSame($this->storageMock, $payum->getTokenStorage());
    }

    public function testShouldAllowGetGatewayFromRegistryInConstructor(): void
    {
        $registry = new SimpleRegistry(
            [
                'foo' => $fooGateway = $this->createMock(GatewayInterface::class),
                'bar' => $barGateway = $this->createMock(GatewayInterface::class),
            ],
            [
                'foo' => 'fooStorage',
                'bar' => 'barStorage',
            ],
            [
                'foo' => 'fooGatewayFactory',
                'bar' => 'barGatewayFactory',
            ]
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock,
        );

        $this->assertSame($fooGateway, $payum->getGateway('foo'));
        $this->assertSame($barGateway, $payum->getGateway('bar'));
        $this->assertSame([
            'foo' => $fooGateway,
            'bar' => $barGateway,
        ], $payum->getGateways());
    }

    public function testShouldAllowGetStoragesFromRegistryInConstructor(): void
    {
        $fooStorage = $this->createMock(StorageInterface::class);
        $barStorage = $this->createMock(StorageInterface::class);

        $registry = new SimpleRegistry(
            [
                'foo' => 'fooGateway',
                'bar' => 'barGateway',
            ],
            [
                'foo' => $fooStorage,
                'bar' => $barStorage,
            ],
            [
                'foo' => 'fooGatewayFactory',
                'bar' => 'barGatewayFactory',
            ]
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock,
        );

        $this->assertSame($fooStorage, $payum->getStorage('foo'));
        $this->assertSame($barStorage, $payum->getStorage('bar'));
        $this->assertSame([
            'foo' => $fooStorage,
            'bar' => $barStorage,
        ], $payum->getStorages());
    }

    public function testShouldAllowGetGatewayFactoriesFromRegistryInConstructor(): void
    {
        $fooGatewayFactory = $this->createMock(GatewayFactoryInterface::class);
        $barGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $registry = new SimpleRegistry(
            [
                'foo' => 'fooGateway',
                'bar' => 'barGateway',
            ],
            [
                'foo' => 'fooStorage',
                'bar' => 'barStorage',
            ],
            [
                'foo' => $fooGatewayFactory,
                'bar' => $barGatewayFactory,
            ]
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock,
        );

        $this->assertSame($fooGatewayFactory, $payum->getGatewayFactory('foo'));
        $this->assertSame($barGatewayFactory, $payum->getGatewayFactory('bar'));
        $this->assertSame([
            'foo' => $fooGatewayFactory,
            'bar' => $barGatewayFactory,
        ], $payum->getGatewayFactories());
    }

    public function testCaptureMethodRepliesHttpRedirect(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $httpRedirect = new HttpRedirect('https://example.com');

        $this->httpRequestVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->willReturn($token);

        $token
            ->expects(self::once())
            ->method('getGatewayName')
            ->willReturn('aGateway');

        $gateway
            ->expects(self::once())
            ->method('execute')
            ->with(new Capture($token))
            ->willReturn($httpRedirect);

        $registry = new SimpleRegistry(
            [
                'aGateway' => $gateway,
            ],
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock
        );

        $response = $payum->capture(Request::create('/capture'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://example.com', $response->getTargetUrl());
    }

    public function testCaptureMethodRepliesHttpPostRedirect(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $httpRedirect = new HttpPostRedirect('example content');

        $this->httpRequestVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->willReturn($token);

        $token
            ->expects(self::once())
            ->method('getGatewayName')
            ->willReturn('aGateway');

        $gateway
            ->expects(self::once())
            ->method('execute')
            ->with(new Capture($token))
            ->willReturn($httpRedirect);

        $registry = new SimpleRegistry(
            [
                'aGateway' => $gateway,
            ],
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock
        );

        $response = $payum->capture(Request::create('/capture'));

        $this->assertNotInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(
            <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <title>Redirecting...</title>
    </head>
    <body onload="document.forms[0].submit();">
        <form action="example content" method="post">
            <p>Redirecting to payment page...</p>
            <p></p>
        </form>
    </body>
</html>
HTML,
            $response->getContent()
        );
    }

    public function testCaptureMethodRepliesRedirectResponse(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $this->httpRequestVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->willReturn($token);

        $token
            ->expects(self::once())
            ->method('getGatewayName')
            ->willReturn('aGateway');

        $token
            ->expects(self::once())
            ->method('getAfterUrl')
            ->willReturn('https://example.com');

        $gateway
            ->expects(self::once())
            ->method('execute')
            ->with(new Capture($token))
            ->willReturn(null);

        $registry = new SimpleRegistry(
            [
                'aGateway' => $gateway,
            ],
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock
        );

        $response = $payum->capture(Request::create('/capture'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://example.com', $response->getTargetUrl());
    }

    public function testDone(): void
    {
        $paymentMock = $this->createMock(PaymentInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $token
            ->expects(self::once())
            ->method('getGatewayName')
            ->willReturn('foo');

        $gateway
            ->expects(self::once())
            ->method('execute')
            ->willReturnCallback(static fn(GetHumanStatus $status) => $status->setModel($paymentMock));

        $this->httpRequestVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with(['payum_token' => 'foo'])
            ->willReturn($token);

        $registry = new SimpleRegistry(
            [
                'foo' => $gateway,
            ],
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock,
        );

        $result = $payum->done(['payum_token' => 'foo']);

        $this->assertSame($paymentMock, $result);
    }

    public function testShouldAllowNotify(): void
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $gateway->expects(self::once())
            ->method('execute')
            ->with(new Notify($token));

        $token->expects(self::once())
            ->method('getGatewayName')
            ->willReturn('foo');

        $this->httpRequestVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with(['payum_token' => 'foo'])
            ->willReturn($token);

        $registry = new SimpleRegistry(
            [
                'foo' => $gateway,
            ],
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock,
        );

        $response = $payum->notify(['payum_token' => 'foo']);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testShouldReturnResponseOnNotifyException(): void
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $gateway->expects(self::once())
            ->method('execute')
            ->willThrowException(
                new HttpResponse('error content', 400)
            );

        $token->expects(self::once())
            ->method('getGatewayName')
            ->willReturn('foo');

        $this->httpRequestVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with(['payum_token' => 'foo'])
            ->willReturn($token);

        $registry = new SimpleRegistry(
            [
                'foo' => $gateway,
            ],
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock,
        );

        $response = $payum->notify(['payum_token' => 'foo']);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('error content', $response->getContent());
    }

    public function testShouldThrowExceptionOnNotifyWithError(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unsupported reply');

        $gateway = $this->createMock(GatewayInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $gateway->expects(self::once())
            ->method('execute')
            ->willThrowException(
                new class extends Exception implements ReplyInterface {
                }
            );

        $token->expects(self::once())
            ->method('getGatewayName')
            ->willReturn('foo');

        $this->httpRequestVerifierMock
            ->expects(self::once())
            ->method('verify')
            ->with(['payum_token' => 'foo'])
            ->willReturn($token);

        $registry = new SimpleRegistry(
            [
                'foo' => $gateway,
            ],
        );

        $payum = new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock,
        );

        $payum->notify(['payum_token' => 'foo']);
    }
}
