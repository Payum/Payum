<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use DI\Container;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Gateway;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\UndescribedGateway;
use Payum\Core\Middleware\IndexReferencesMiddleware;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Model\Payment;
use Payum\Core\Reference\InMemoryReferenceIndex;
use Payum\Core\Reference\ReferenceIndexInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class IndexReferencesMiddlewareTest extends TestCase
{
    public function testShouldIndexTheTransactionIdOfASavedPayment(): void
    {
        $identity = $this->createMock(IdentityInterface::class);
        $identity->method('getId')->willReturn(7);
        $identity->method('getClass')->willReturn(Payment::class);
        $index = new InMemoryReferenceIndex();

        $this->dispatch($index, $identity, CaptureResult::captured('pi_1'));

        $record = $index->find('acme', 'pi_1');
        $this->assertNotNull($record);
        $this->assertSame('acme', $record->getGatewayName());
        $this->assertSame('7', $record->getIdentity()->getId());
    }

    public function testShouldSkipASubjectWithNoIdentity(): void
    {
        $index = $this->createMock(ReferenceIndexInterface::class);
        $index->expects($this->never())->method('record');

        $this->dispatch($index, null, CaptureResult::captured('pi_1'));
    }

    public function testShouldSkipAResultWithNoTransactionId(): void
    {
        $identity = $this->createMock(IdentityInterface::class);
        $identity->method('getId')->willReturn(7);
        $identity->method('getClass')->willReturn(Payment::class);
        $index = $this->createMock(ReferenceIndexInterface::class);
        $index->expects($this->never())->method('record');

        $this->dispatch($index, $identity, CaptureResult::captured());
    }

    public function testShouldBeInTheDefaultPipeline(): void
    {
        $resolved = CoreGatewayFactory::defaultMiddleware()->resolve(
            new Container((new CoreGatewayFactory())->createConfig([]))
        );

        $this->assertContains(IndexReferencesMiddleware::class, array_map(static fn (object $m): string => $m::class, $resolved));
    }

    private function dispatch(ReferenceIndexInterface $index, ?IdentityInterface $identity, CaptureResult $result): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('support')->willReturn(true);
        $storage->method('identify')->willReturn($identity);
        $registry = $this->createMock(StorageRegistryInterface::class);
        $registry->method('getStorages')->willReturn($identity instanceof IdentityInterface ? [$storage] : []);

        $command = CaptureCommand::forToken($this->createMock(TokenInterface::class));

        (new Pipeline([new IndexReferencesMiddleware($index, $registry)]))->process(
            $command,
            new Context(
                $this->createMock(Gateway::class),
                $command,
                new UndescribedGateway('acme'),
                $this->createMock(ServerRequestInterface::class),
                $this->createMock(TokenIssuerInterface::class),
                new Payment(),
                null,
                [],
                'acme',
            ),
            static fn (): CaptureResult => $result,
        );
    }
}
