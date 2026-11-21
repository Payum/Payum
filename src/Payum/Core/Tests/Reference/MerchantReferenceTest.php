<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Reference;

use Doctrine\Persistence\Proxy;
use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway as Executor;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Reference\InMemoryReferenceIndex;
use Payum\Core\Reference\MerchantReference;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Storage\FilesystemStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class MerchantReferenceTest extends TestCase
{
    public function testShouldBeStableForTheSameSubject(): void
    {
        $this->assertSame(
            MerchantReference::of(new Identity('7', Payment::class)),
            MerchantReference::of(new Identity('7', Payment::class)),
        );
    }

    public function testShouldDifferBetweenSubjects(): void
    {
        $this->assertNotSame(
            MerchantReference::of(new Identity('7', Payment::class)),
            MerchantReference::of(new Identity('8', Payment::class)),
        );
    }

    public function testShouldFitThePspFieldLimits(): void
    {
        $reference = MerchantReference::of(new Identity('7', Payment::class));

        $this->assertSame(35, strlen($reference));
        $this->assertTrue(str_starts_with($reference, 'py_'));
    }

    public function testShouldTreatAProxyAsItsParent(): void
    {
        $this->assertSame(
            MerchantReference::of(new Identity('7', Payment::class)),
            MerchantReference::of(new Identity('7', ProxiedPayment::class)),
        );
    }

    public function testShouldRefuseAnIdentityWithoutAnId(): void
    {
        $this->expectException(LogicException::class);

        MerchantReference::of(new Identity('', Payment::class));
    }

    public function testContextShouldRefuseWhenThereIsNoIndex(): void
    {
        $this->expectException(LogicException::class);

        $this->context(null, 'acme')->merchantReference();
    }

    public function testContextShouldRefuseWhenTheGatewayHasNoName(): void
    {
        $this->expectException(LogicException::class);

        $this->context(new InMemoryReferenceIndex(), null)->merchantReference();
    }

    public function testContextShouldRecordItInTheIndex(): void
    {
        $index = new InMemoryReferenceIndex();
        $payum = $this->payum($index);
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payum->getStorage(Payment::class)->update($payment);
        ReferencingHandler::$reference = null;

        $payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        $record = $index->find(MerchantReference::NAMESPACE, (string) ReferencingHandler::$reference);
        $this->assertNotNull($record);
        $this->assertSame('acme', $record->getGatewayName());
        $this->assertSame((string) $payment->getNumber(), $record->getSubjectId());
    }

    public function testContextShouldRefuseAnUnsavedSubject(): void
    {
        $payment = new Payment();

        $this->expectException(LogicException::class);

        $this->payum()->getGateway('acme')->execute(CaptureCommand::forPayment($payment));
    }

    private function context(?InMemoryReferenceIndex $index, ?string $name): Context
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $storages = $this->createMock(StorageRegistryInterface::class);
        $storages->method('getStorages')->willReturn([new FilesystemStorage(sys_get_temp_dir(), Payment::class, 'number')]);

        return new Context(
            $this->createMock(Executor::class),
            CaptureCommand::forPayment($payment),
            $this->createMock(PaymentGateway::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(TokenIssuerInterface::class),
            $payment,
            null,
            [],
            $name,
            $index,
            $storages,
        );
    }

    /**
     * @return Payum<object>
     */
    private function payum(?InMemoryReferenceIndex $index = null): Payum
    {
        return (new PayumBuilder())
            ->setReferenceIndex($index ?? new InMemoryReferenceIndex())
            ->addDefaultStorages()
            ->registerGateway('acme', new ReferencingConfig())
            ->getPayum();
    }
}

final class ReferencingConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return ReferencingGateway::class;
    }
}

final class ReferencingGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return ReferencingConfig::class;
    }

    public function handlers(): array
    {
        return [ReferencingHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class ReferencingHandler implements CaptureHandlerInterface
{
    public static ?string $reference = null;

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        self::$reference = $context->merchantReference();

        return CaptureResult::captured('txn_1');
    }
}

/**
 * @implements Proxy<Payment>
 */
final class ProxiedPayment extends Payment implements Proxy
{
    public function __load(): void
    {
    }

    public function __isInitialized(): bool
    {
        return true;
    }
}
