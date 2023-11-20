<?php

namespace Payum\Core\Tests\Registry;

use Exception;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\FallbackRegistry;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FallbackRegistryTest extends TestCase
{
    public function testShouldImplementsRegistryInterface(): void
    {
        $rc = new ReflectionClass(FallbackRegistry::class);

        $this->assertTrue($rc->implementsInterface(RegistryInterface::class));
    }

    public function testShouldReturnGatewayFromMainRegistry(): void
    {
        $expectedGateway = $this->createMock(GatewayInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->willReturn($expectedGateway)
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getGateway')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGateway, $registry->getGateway('theGatewayName'));
    }

    public function testShouldTryFallbackIfInvalidArgumentExceptionThrownFromMainRegistryOnGetGateway(): void
    {
        $expectedGateway = $this->createMock(GatewayInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->willThrowException(new InvalidArgumentException())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->willReturn($expectedGateway)
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGateway, $registry->getGateway('theGatewayName'));
    }

    public function testThrowIfBothRegistriesNotContainsGateway(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('second');
        $expectedGateway = $this->createMock(GatewayInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->willThrowException(new InvalidArgumentException('first'))
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->willThrowException(new InvalidArgumentException('second'))
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGateway, $registry->getGateway('theGatewayName'));
    }

    public function testShouldNotCatchNoInvalidArgumentExceptionsFromMainRegistryOnGetGateway(): void
    {
        $this->expectException(Exception::class);
        $expectedGateway = $this->createMock(GatewayInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->willThrowException(new Exception())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getGateway')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGateway, $registry->getGateway('theGatewayName'));
    }

    public function testShouldReturnStorageFromMainRegistry(): void
    {
        $expectedStorage = $this->createMock(StorageInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($expectedStorage::class)
            ->willReturn($expectedStorage)
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedStorage, $registry->getStorage($expectedStorage::class));
    }

    public function testShouldTryFallbackIfInvalidArgumentExceptionThrownFromMainRegistryOnGetStorage(): void
    {
        $this->createMock(StorageInterface::class);
        $expectedStorage = $this->createMock(StorageInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($expectedStorage::class)
            ->willThrowException(new InvalidArgumentException())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($expectedStorage::class)
            ->willReturn($expectedStorage)
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedStorage, $registry->getStorage($expectedStorage::class));
    }

    public function testThrowIfBothRegistriesNotContainsStorage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('second');
        $expectedStorage = $this->createMock(StorageInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($expectedStorage::class)
            ->willThrowException(new InvalidArgumentException('first'))
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($expectedStorage::class)
            ->willThrowException(new InvalidArgumentException('second'))
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedStorage, $registry->getStorage($expectedStorage::class));
    }

    public function testShouldNotCatchNoInvalidArgumentExceptionsFromMainRegistryOnGetStorage(): void
    {
        $this->expectException(Exception::class);
        $expectedStorage = $this->createMock(StorageInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($expectedStorage::class)
            ->willThrowException(new Exception())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedStorage, $registry->getStorage($expectedStorage::class));
    }

    public function testShouldReturnGatewayFactoryFromMainRegistry(): void
    {
        $expectedGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with('theGatewayFactoryName')
            ->willReturn($expectedGatewayFactory)
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getGatewayFactory')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGatewayFactory, $registry->getGatewayFactory('theGatewayFactoryName'));
    }

    public function testShouldTryFallbackIfInvalidArgumentExceptionThrownFromMainRegistryOnGetGatewayFactory(): void
    {
        $expectedGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with('theGatewayFactoryName')
            ->willThrowException(new InvalidArgumentException())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with('theGatewayFactoryName')
            ->willReturn($expectedGatewayFactory)
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGatewayFactory, $registry->getGatewayFactory('theGatewayFactoryName'));
    }

    public function testThrowIfBothRegistriesNotContainsGatewayFactory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('second');
        $expectedGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with('theGatewayFactoryName')
            ->willThrowException(new InvalidArgumentException('first'))
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with('theGatewayFactoryName')
            ->willThrowException(new InvalidArgumentException('second'))
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGatewayFactory, $registry->getGatewayFactory('theGatewayFactoryName'));
    }

    public function testShouldNotCatchNoInvalidArgumentExceptionsFromMainRegistryOnGetGatewayFactory(): void
    {
        $this->expectException(Exception::class);
        $expectedGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with('theGatewayFactoryName')
            ->willThrowException(new Exception())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getGatewayFactory')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGatewayFactory, $registry->getGatewayFactory('theGatewayFactoryName'));
    }

    public function testShouldMergeGatewaysFromMainAndFallbackRegistries(): void
    {
        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGateways')
            ->willReturn([
                'foo' => 'fooMain',
                'bar' => 'barMain',
                'baz' => 'bazMain',
            ])
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getGateways')
            ->willReturn([
                'foo' => 'fooFallback',
                'ololo' => 'ololoFallback',
            ])
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame([
            'foo' => 'fooMain',
            'ololo' => 'ololoFallback',
            'bar' => 'barMain',
            'baz' => 'bazMain',
        ], $registry->getGateways());
    }

    public function testShouldMergeStoragesFromMainAndFallbackRegistries(): void
    {
        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getStorages')
            ->willReturn([
                'foo' => 'fooMain',
                'bar' => 'barMain',
                'baz' => 'bazMain',
            ])
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getStorages')
            ->willReturn([
                'foo' => 'fooFallback',
                'ololo' => 'ololoFallback',
            ])
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame([
            'foo' => 'fooMain',
            'ololo' => 'ololoFallback',
            'bar' => 'barMain',
            'baz' => 'bazMain',
        ], $registry->getStorages());
    }

    public function testShouldMergeGatewayFactoriesFromMainAndFallbackRegistries(): void
    {
        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactories')
            ->willReturn([
                'foo' => 'fooMain',
                'bar' => 'barMain',
                'baz' => 'bazMain',
            ])
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactories')
            ->willReturn([
                'foo' => 'fooFallback',
                'ololo' => 'ololoFallback',
            ])
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame([
            'foo' => 'fooMain',
            'ololo' => 'ololoFallback',
            'bar' => 'barMain',
            'baz' => 'bazMain',
        ], $registry->getGatewayFactories());
    }

    /**
     * @return MockObject|RegistryInterface<object>
     */
    protected function createRegistryMock(): MockObject | RegistryInterface
    {
        return $this->createMock(RegistryInterface::class);
    }
}
