<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use Exception;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\Poll;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Template\RendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use function spl_object_hash;

final class PayumTest extends TestCase
{
    /**
     * @var RegistryInterface<StorageRegistryInterface<StorageInterface<TokenInterface>>>|MockObject
     */
    private RegistryInterface | MockObject $registryMock;

    private HttpRequestVerifierInterface | MockObject $httpRequestVerifierMock;

    private GenericTokenFactoryInterface | MockObject $tokenFactoryMock;

    /**
     * @var StorageInterface<TokenInterface>|MockObject
     */
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
        $fooStorage = $this->createMock(StorageInterface::class);
        $barStorage = $this->createMock(StorageInterface::class);

        $registry = new SimpleRegistry(
            [
                'foo' => $fooGateway = $this->createMock(GatewayInterface::class),
                'bar' => $barGateway = $this->createMock(GatewayInterface::class),
            ],
            [
                $fooStorage::class => $fooStorage,
                $barStorage::class => $barStorage,
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
                spl_object_hash($fooStorage) => $fooStorage,
                spl_object_hash($barStorage) => $barStorage,
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

        $this->assertSame($fooStorage, $payum->getStorage(spl_object_hash($fooStorage)));
        $this->assertSame($barStorage, $payum->getStorage(spl_object_hash($barStorage)));
        $this->assertSame([
            spl_object_hash($fooStorage) => $fooStorage,
            spl_object_hash($barStorage) => $barStorage,
        ], $payum->getStorages());
    }

    public function testShouldAllowGetGatewayFactoriesFromRegistryInConstructor(): void
    {
        $fooStorage = $this->createMock(StorageInterface::class);
        $barStorage = $this->createMock(StorageInterface::class);

        $fooGatewayFactory = $this->createMock(GatewayFactoryInterface::class);
        $barGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $registry = new SimpleRegistry(
            [
                'foo' => 'fooGateway',
                'bar' => 'barGateway',
            ],
            [
                spl_object_hash($fooStorage) => $fooStorage,
                spl_object_hash($barStorage) => $barStorage,
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

    public function testCaptureMethodThrowsForANextActionItCannotTurnIntoAResponse(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(Gateway::class);

        $this->httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->willReturn($token);

        $token
            ->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('aGateway');

        $gateway
            ->expects($this->once())
            ->method('supportsCommand')
            ->willReturn(true);

        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturn(CaptureResult::pending(new Poll(30)));

        // Redirecting to the after URL here would tell the application the payment finished.
        $token
            ->expects($this->never())
            ->method('getAfterUrl');

        $payum = new Payum(
            new SimpleRegistry([
                'aGateway' => $gateway,
            ]),
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(Poll::class);

        $payum->capture(Request::create('/capture'));
    }

    public function testCaptureMethodRendersATemplateNextAction(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(Gateway::class);
        $renderer = $this->createMock(RendererInterface::class);

        $this->httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->willReturn($token);

        $token
            ->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('aGateway');

        // The payment is not finished, so the customer must not be sent on to the after URL.
        $token
            ->expects($this->never())
            ->method('getAfterUrl');

        $gateway
            ->expects($this->once())
            ->method('supportsCommand')
            ->willReturn(true);

        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturn(CaptureResult::pending(new RenderTemplate('@Acme/form.html.twig', [
                'amount' => 123,
            ])));

        $gateway
            ->expects($this->once())
            ->method('renderer')
            ->willReturn($renderer);

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with('@Acme/form.html.twig', [
                'amount' => 123,
            ])
            ->willReturn('<form>pay</form>');

        $payum = new Payum(
            new SimpleRegistry([
                'aGateway' => $gateway,
            ]),
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock
        );

        $response = $payum->capture(Request::create('/capture'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('<form>pay</form>', $response->getContent());
    }

    public function testCaptureMethodRedirectsToTheAfterUrlWhenTheCommandIsFinished(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(Gateway::class);

        $this->httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->willReturn($token);

        $token
            ->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('aGateway');

        $token
            ->expects($this->once())
            ->method('getAfterUrl')
            ->willReturn('https://example.com/done');

        $gateway
            ->expects($this->once())
            ->method('supportsCommand')
            ->willReturn(true);

        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturn(CaptureResult::captured('txn_1'));

        $payum = new Payum(
            new SimpleRegistry([
                'aGateway' => $gateway,
            ]),
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock
        );

        $response = $payum->capture(Request::create('/capture'));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://example.com/done', $response->getTargetUrl());
    }

    public function testCaptureMethodRepliesHttpRedirect(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $httpRedirect = new HttpRedirect('https://example.com');

        $this->httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->willReturn($token);

        $token
            ->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('aGateway');

        $gateway
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('verify')
            ->willReturn($token);

        $token
            ->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('aGateway');

        $gateway
            ->expects($this->once())
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
HTML
            ,
            $response->getContent()
        );
    }

    public function testCaptureMethodRepliesRedirectResponse(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $this->httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->willReturn($token);

        $token
            ->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('aGateway');

        $token
            ->expects($this->once())
            ->method('getAfterUrl')
            ->willReturn('https://example.com');

        $gateway
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('foo');

        $gateway
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(static fn (GetHumanStatus $status) => $status->setModel($paymentMock));

        $this->httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->with([
                'payum_token' => 'foo',
            ])
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

        $result = $payum->done([
            'payum_token' => 'foo',
        ]);

        $this->assertSame($paymentMock, $result);
    }

    public function testShouldAllowNotify(): void
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $gateway->expects($this->once())
            ->method('execute')
            ->with(new Notify($token));

        $token->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('foo');

        $this->httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->with([
                'payum_token' => 'foo',
            ])
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

        $response = $payum->notify([
            'payum_token' => 'foo',
        ]);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testShouldReturnResponseOnNotifyException(): void
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $gateway->expects($this->once())
            ->method('execute')
            ->willThrowException(
                new HttpResponse('error content', 400)
            );

        $token->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('foo');

        $this->httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->with([
                'payum_token' => 'foo',
            ])
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

        $response = $payum->notify([
            'payum_token' => 'foo',
        ]);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('error content', $response->getContent());
    }

    public function testShouldThrowExceptionOnNotifyWithError(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unsupported reply');

        $gateway = $this->createMock(GatewayInterface::class);
        $token = $this->createMock(TokenInterface::class);

        $gateway->expects($this->once())
            ->method('execute')
            ->willThrowException(
                new class() extends Exception implements ReplyInterface {
                }
            );

        $token->expects($this->once())
            ->method('getGatewayName')
            ->willReturn('foo');

        $this->httpRequestVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->with([
                'payum_token' => 'foo',
            ])
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

        $payum->notify([
            'payum_token' => 'foo',
        ]);
    }

    public function testPrepareReturnsTheCaptureTokenBuiltFromTheGatewayNameAndModel(): void
    {
        $payment = new Payment();
        $token = $this->createMock(TokenInterface::class);

        $this->tokenFactoryMock
            ->expects($this->once())
            ->method('createCaptureToken')
            ->with('aGateway', $payment, 'done.php', [])
            ->willReturn($token);

        $payum = $this->createPayumWithStorageFor(Payment::class);

        $this->assertSame($token, $payum->prepare('aGateway', $payment, 'done.php'));
    }

    public function testPrepareUpdatesTheModelInTheStorageResolvedForItsOwnClass(): void
    {
        $payment = new Payment();

        $this->storageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($payment))
            ->willReturn($payment);

        $this->tokenFactoryMock
            ->method('createCaptureToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $payum = $this->createPayumWithStorageFor(Payment::class);

        $payum->prepare('aGateway', $payment);
    }

    public function testPrepareUpdatesTheModelBeforeCreatingTheToken(): void
    {
        $payment = new Payment();
        $calls = [];

        $this->storageMock
            ->method('update')
            ->willReturnCallback(function (object $model) use (&$calls): object {
                $calls[] = 'update';

                return $model;
            });

        $this->tokenFactoryMock
            ->method('createCaptureToken')
            ->willReturnCallback(function () use (&$calls): TokenInterface {
                $calls[] = 'createCaptureToken';

                return $this->createMock(TokenInterface::class);
            });

        $payum = $this->createPayumWithStorageFor(Payment::class);

        $payum->prepare('aGateway', $payment);

        $this->assertSame(['update', 'createCaptureToken'], $calls);
    }

    public function testPrepareFallsBackToTheDefaultAfterPathFromTheConstructor(): void
    {
        $payment = new Payment();

        $this->tokenFactoryMock
            ->expects($this->once())
            ->method('createCaptureToken')
            ->with('aGateway', $payment, 'complete.php', [])
            ->willReturn($this->createMock(TokenInterface::class));

        $payum = $this->createPayumWithStorageFor(Payment::class, 'complete.php');

        $payum->prepare('aGateway', $payment, 'complete.php');
    }

    public function testPrepareUsesDonePhpWhenNoDefaultAfterPathIsGiven(): void
    {
        $payment = new Payment();

        $this->tokenFactoryMock
            ->expects($this->once())
            ->method('createCaptureToken')
            ->with('aGateway', $payment, 'done.php', [])
            ->willReturn($this->createMock(TokenInterface::class));

        $payum = $this->createPayumWithStorageFor(Payment::class);

        $payum->prepare('aGateway', $payment, 'done.php');
    }

    public function testPrepareAllowsAnExplicitAfterPathToOverrideTheDefault(): void
    {
        $payment = new Payment();

        $this->tokenFactoryMock
            ->expects($this->once())
            ->method('createCaptureToken')
            ->with('aGateway', $payment, 'thanks.php', [])
            ->willReturn($this->createMock(TokenInterface::class));

        $payum = $this->createPayumWithStorageFor(Payment::class, 'complete.php');

        $payum->prepare('aGateway', $payment, 'thanks.php');
    }

    public function testPrepareForwardsTheAfterParameters(): void
    {
        $payment = new Payment();

        $this->tokenFactoryMock
            ->expects($this->once())
            ->method('createCaptureToken')
            ->with('aGateway', $payment, 'thanks.php', [
                'order' => 'anId',
            ])
            ->willReturn($this->createMock(TokenInterface::class));

        $payum = $this->createPayumWithStorageFor(Payment::class);

        $payum->prepare('aGateway', $payment, 'thanks.php', [
            'order' => 'anId',
        ]);
    }

    public function testPrepareAcceptsAModelWhichIsNotAPaymentInterface(): void
    {
        $details = new ArrayObject();
        $token = $this->createMock(TokenInterface::class);

        $this->storageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($details))
            ->willReturn($details);

        $this->tokenFactoryMock
            ->expects($this->once())
            ->method('createCaptureToken')
            ->with('aGateway', $details, 'done.php', [])
            ->willReturn($token);

        $payum = $this->createPayumWithStorageFor(ArrayObject::class);

        $this->assertSame($token, $payum->prepare('aGateway', $details, 'done.php'));
    }

    public function testPrepareThrowsWhenTheModelHasNoRegisteredStorage(): void
    {
        $this->tokenFactoryMock
            ->expects($this->never())
            ->method('createCaptureToken');

        $payum = $this->createPayumWithStorageFor(ArrayObject::class);

        $this->expectException(InvalidArgumentException::class);

        $payum->prepare('aGateway', new Payment());
    }

    /**
     * @param class-string $modelClass
     *
     * @return Payum<object>
     */
    private function createPayumWithStorageFor(string $modelClass, ?string $defaultAfterPath = null): Payum
    {
        $registry = new SimpleRegistry([], [
            $modelClass => $this->storageMock,
        ]);

        return new Payum(
            $registry,
            $this->httpRequestVerifierMock,
            $this->tokenFactoryMock,
            $this->storageMock,
            ...(null === $defaultAfterPath ? [] : [$defaultAfterPath])
        );
    }
}
