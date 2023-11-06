<?php
namespace Payum\Core\Tests\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Model\GatewayConfig;
use Payum\Core\Gateway;
use Payum\Core\Registry\FallbackRegistry;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

class FallbackRegistryTest extends TestCase
{
    public function testShouldImplementsRegistryInterface()
    {
        $rc = new \ReflectionClass(FallbackRegistry::class);

        $this->assertTrue($rc->implementsInterface(RegistryInterface::class));
    }

    public function testShouldReturnGatewayFromMainRegistry()
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

    public function testShouldTryFallbackIfInvalidArgumentExceptionThrownFromMainRegistryOnGetGateway()
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

    public function testThrowIfBothRegistriesNotContainsGateway()
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

    public function testShouldNotCatchNoInvalidArgumentExceptionsFromMainRegistryOnGetGateway()
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

    public function testShouldReturnStorageFromMainRegistry()
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

    public function testShouldTryFallbackIfInvalidArgumentExceptionThrownFromMainRegistryOnGetStorage()
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

    public function testThrowIfBothRegistriesNotContainsStorage()
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

    public function testShouldNotCatchNoInvalidArgumentExceptionsFromMainRegistryOnGetStorage()
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

    public function testShouldReturnGatewayFactoryFromMainRegistry()
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

    public function testShouldTryFallbackIfInvalidArgumentExceptionThrownFromMainRegistryOnGetGatewayFactory()
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

    public function testThrowIfBothRegistriesNotContainsGatewayFactory()
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

    public function testShouldNotCatchNoInvalidArgumentExceptionsFromMainRegistryOnGetGatewayFactory()
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

    public function testShouldMergeGatewaysFromMainAndFallbackRegistries()
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

    public function testShouldMergeStoragesFromMainAndFallbackRegistries()
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

    public function testShouldMergeGatewayFactoriesFromMainAndFallbackRegistries()
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
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    protected function createRegistryMock()
    {
        return $this->createMock('Payum\Core\Registry\RegistryInterface');
    }
}
