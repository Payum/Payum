<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\LogicException;
use Payum\Core\Extension\Context as ExtensionContext;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Gateway;
use Payum\Core\Gateway\DeclaresMiddleware;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Metadata\Logo;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Result;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

final class MiddlewarePipelineTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        Recorder::$calls = [];
    }

    public function testShouldRunMiddlewareTheApplicationRegistered(): void
    {
        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment(new Payment()));

        $this->assertContains('global:before', Recorder::$calls);
        $this->assertContains('global:after', Recorder::$calls);
    }

    public function testShouldRunMiddlewareTheGatewayDeclared(): void
    {
        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment(new Payment()));

        $this->assertContains('gateway:before', Recorder::$calls);
        $this->assertContains('gateway:after', Recorder::$calls);
    }

    public function testShouldRunApplicationMiddlewareOutsideTheGatewaysOwn(): void
    {
        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment(new Payment()));

        $this->assertSame(
            ['global:before', 'gateway:before', 'handler', 'gateway:after', 'global:after'],
            Recorder::$calls,
        );
    }

    public function testShouldHonourPriorityOverRegistrationOrder(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            // Registered first, but asks to sit further in.
            ->addMiddleware(new RecordingMiddleware('inner'), -100)
            ->addMiddleware(new RecordingMiddleware('outer'), 100)
            ->registerGateway('acme', new PipelineConfig())
            ->getPayum();

        $payum->getGateway('acme')->execute(CaptureCommand::forPayment(new Payment()));

        $order = array_values(array_filter(
            Recorder::$calls,
            static fn (string $call): bool => str_starts_with($call, 'outer') || str_starts_with($call, 'inner'),
        ));

        $this->assertSame(['outer:before', 'inner:before', 'inner:after', 'outer:after'], $order);
    }

    public function testShouldStillWriteStateOntoThePayment(): void
    {
        $payment = new Payment();

        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        // PersistStateMiddleware took over what the executor used to do inline.
        $this->assertSame([
            'checkout_id' => 'chk_1',
        ], $payment->getDetails());
    }

    public function testShouldWriteStateEvenWhenTheHandlerThrows(): void
    {
        $payment = new Payment();
        $payment->setDetails([
            'explode' => true,
        ]);

        try {
            $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

            $this->fail('Expected the handler to throw.');
        } catch (\RuntimeException) {
            // A checkout id written before the failure has to survive, or the retry opens a second one.
            $this->assertSame('chk_1', $payment->getDetails()['checkout_id']);
        }
    }

    public function testShouldRunRegisteredExtensionsThroughTheBridge(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');
        $extension = new RecordingExtension();

        $this->assertInstanceOf(Gateway::class, $gateway);
        $gateway->addExtension($extension);
        $gateway->execute(CaptureCommand::forPayment(new Payment()));

        $this->assertSame(['onPreExecute', 'onExecute', 'onPostExecute'], $extension->calls);
        $this->assertInstanceOf(CaptureCommand::class, $extension->seenRequest);
    }

    public function testShouldGiveAnExtensionTheExceptionAHandlerThrew(): void
    {
        $payment = new Payment();
        $payment->setDetails([
            'explode' => true,
        ]);

        $gateway = $this->buildPayum()->getGateway('acme');
        $extension = new RecordingExtension();

        $this->assertInstanceOf(Gateway::class, $gateway);
        $gateway->addExtension($extension);

        try {
            $gateway->execute(CaptureCommand::forPayment($payment));

            $this->fail('Expected the handler to throw.');
        } catch (\RuntimeException) {
            $this->assertContains('onPostExecute', $extension->calls);
            $this->assertInstanceOf(\RuntimeException::class, $extension->seenException);
        }
    }

    public function testShouldStopAHandlerThatDispatchesItsWayIntoALoop(): void
    {
        $payment = new Payment();
        $payment->setDetails([
            'recurse' => true,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Possible endless cycle detected');

        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($payment));
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            ->addMiddleware(new RecordingMiddleware('global'))
            ->registerGateway('acme', new PipelineConfig())
            ->getPayum();
    }
}

final class Recorder
{
    /**
     * @var list<string>
     */
    public static array $calls = [];
}

final class RecordingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $name
    ) {
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        Recorder::$calls[] = $this->name . ':before';

        try {
            return $next($command, $context);
        } finally {
            Recorder::$calls[] = $this->name . ':after';
        }
    }
}

final class RecordingExtension implements ExtensionInterface
{
    /**
     * @var list<string>
     */
    public array $calls = [];

    public mixed $seenRequest = null;

    public ?\Exception $seenException = null;

    public function onPreExecute(ExtensionContext $context): void
    {
        $this->calls[] = 'onPreExecute';
        $this->seenRequest = $context->getRequest();
    }

    public function onExecute(ExtensionContext $context): void
    {
        $this->calls[] = 'onExecute';
    }

    public function onPostExecute(ExtensionContext $context): void
    {
        $this->calls[] = 'onPostExecute';
        $this->seenException = $context->getException();
    }
}

final class PipelineConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return PipelineGateway::class;
    }
}

final class PipelineGateway implements PaymentGateway, DeclaresMiddleware
{
    public function configClass(): string
    {
        return PipelineConfig::class;
    }

    public function handlers(): array
    {
        return [PipelineCaptureHandler::class];
    }

    public function middleware(): array
    {
        return [GatewayMiddleware::class];
    }

    public function logo(): Logo
    {
        return Logo\Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Payments';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class GatewayMiddleware implements MiddlewareInterface
{
    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        Recorder::$calls[] = 'gateway:before';

        try {
            return $next($command, $context);
        } finally {
            Recorder::$calls[] = 'gateway:after';
        }
    }
}

final class PipelineCaptureHandler implements CaptureHandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        Recorder::$calls[] = 'handler';

        $state = $context->state();
        $state['checkout_id'] = 'chk_1';

        if ($state['recurse']) {
            $context->execute(CaptureCommand::forPayment($context->payment()));
        }

        if ($state['explode']) {
            throw new \RuntimeException('psp exploded');
        }

        return CaptureResult::captured('txn_1');
    }
}
