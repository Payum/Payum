<?php

namespace Payum\Core\Tests\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Gateway;
use Payum\Core\Model\GatewayConfig;
use Payum\Core\Registry\FallbackRegistry;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

class FallbackRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementsRegistryInterface()
    {
        $rc = new \ReflectionClass(FallbackRegistry::class);

        $this->assertTrue($rc->implementsInterface(RegistryInterface::class));
    }

    /**
     * @test
     */
    public function shouldReturnGatewayFromMainRegistry()
    {
        $expectedGateway = new \stdClass();

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

    /**
     * @test
     */
    public function shouldTryFallbackIfInvalidArgumentExceptionThrownFromMainRegistryOnGetGateway()
    {
        $expectedGateway = new \stdClass();

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

    /**
     * @test
     */
    public function throwIfBothRegistriesNotContainsGateway()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('second');
        $expectedGateway = new \stdClass();

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

    /**
     * @test
     */
    public function shouldNotCatchNoInvalidArgumentExceptionsFromMainRegistryOnGetGateway()
    {
        $this->expectException(\Exception::class);
        $expectedGateway = new \stdClass();

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->willThrowException(new \Exception())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getGateway')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGateway, $registry->getGateway('theGatewayName'));
    }

    /**
     * @test
     */
    public function shouldReturnStorageFromMainRegistry()
    {
        $expectedStorage = new \stdClass();

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with('theStorageName')
            ->willReturn($expectedStorage)
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedStorage, $registry->getStorage('theStorageName'));
    }

    /**
     * @test
     */
    public function shouldTryFallbackIfInvalidArgumentExceptionThrownFromMainRegistryOnGetStorage()
    {
        $expectedStorage = new \stdClass();

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with('theStorageName')
            ->willThrowException(new InvalidArgumentException())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with('theStorageName')
            ->willReturn($expectedStorage)
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedStorage, $registry->getStorage('theStorageName'));
    }

    /**
     * @test
     */
    public function throwIfBothRegistriesNotContainsStorage()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('second');
        $expectedStorage = new \stdClass();

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with('theStorageName')
            ->willThrowException(new InvalidArgumentException('first'))
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with('theStorageName')
            ->willThrowException(new InvalidArgumentException('second'))
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedStorage, $registry->getStorage('theStorageName'));
    }

    /**
     * @test
     */
    public function shouldNotCatchNoInvalidArgumentExceptionsFromMainRegistryOnGetStorage()
    {
        $this->expectException(\Exception::class);
        $expectedStorage = new \stdClass();

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with('theStorageName')
            ->willThrowException(new \Exception())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedStorage, $registry->getStorage('theStorageName'));
    }

    /**
     * @test
     */
    public function shouldReturnGatewayFactoryFromMainRegistry()
    {
        $expectedGatewayFactory = new \stdClass();

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

    /**
     * @test
     */
    public function shouldTryFallbackIfInvalidArgumentExceptionThrownFromMainRegistryOnGetGatewayFactory()
    {
        $expectedGatewayFactory = new \stdClass();

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

    /**
     * @test
     */
    public function throwIfBothRegistriesNotContainsGatewayFactory()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('second');
        $expectedGatewayFactory = new \stdClass();

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

    /**
     * @test
     */
    public function shouldNotCatchNoInvalidArgumentExceptionsFromMainRegistryOnGetGatewayFactory()
    {
        $this->expectException(\Exception::class);
        $expectedGatewayFactory = new \stdClass();

        $mailRegistryMock = $this->createRegistryMock();
        $mailRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with('theGatewayFactoryName')
            ->willThrowException(new \Exception())
        ;

        $fallbackRegistryMock = $this->createRegistryMock();
        $fallbackRegistryMock
            ->expects($this->never())
            ->method('getGatewayFactory')
        ;

        $registry = new FallbackRegistry($mailRegistryMock, $fallbackRegistryMock);

        $this->assertSame($expectedGatewayFactory, $registry->getGatewayFactory('theGatewayFactoryName'));
    }

    /**
     * @test
     */
    public function shouldMergeGatewaysFromMainAndFallbackRegistries()
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

    /**
     * @test
     */
    public function shouldMergeStoragesFromMainAndFallbackRegistries()
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

    /**
     * @test
     */
    public function shouldMergeGatewayFactoriesFromMainAndFallbackRegistries()
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
     * @return \PHPUnit\Framework\MockObject\MockObject|RegistryInterface
     */
    protected function createRegistryMock()
    {
        return $this->createMock('Payum\Core\Registry\RegistryInterface');
    }
}
