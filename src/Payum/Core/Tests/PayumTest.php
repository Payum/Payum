<?php

namespace Payum\Core\Tests;

use Payum\Core\GatewayInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\Storage\MockStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class PayumTest extends TestCase
{
    public function testShouldImplementRegistryInterface()
    {
        $rc = new ReflectionClass(Payum::class);

        $this->assertTrue($rc->implementsInterface(RegistryInterface::class));
    }

    public function testShouldAllowGetHttpRequestVerifierSetInConstructor()
    {
        $httpRequestVerifier = $this->createHttpRequestVerifierMock();

        $payum = new Payum(
            $this->createRegistryMock(),
            $httpRequestVerifier,
            $this->createGenericTokenFactoryMock(),
            $this->createTokenStorage()
        );

        $this->assertSame($httpRequestVerifier, $payum->getHttpRequestVerifier());
    }

    public function testShouldAllowGetGenericTokenFactorySetInConstructor()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $payum = new Payum(
            $this->createRegistryMock(),
            $this->createHttpRequestVerifierMock(),
            $tokenFactory,
            $this->createTokenStorage()
        );

        $this->assertSame($tokenFactory, $payum->getTokenFactory());
    }

    public function testShouldAllowGetTokenStorageSetInConstructor()
    {
        $tokenStorage = $this->createTokenStorage();

        $payum = new Payum(
            $this->createRegistryMock(),
            $this->createHttpRequestVerifierMock(),
            $this->createGenericTokenFactoryMock(),
            $tokenStorage
        );

        $this->assertSame($tokenStorage, $payum->getTokenStorage());
    }

    public function testShouldAllowGetGatewayFromRegistryInConstructor()
    {
        /** @var MockObject | StorageInterface<stdClass> $storageOne */
        $storageOne = $this->createMock(StorageInterface::class);

        /** @var MockObject | StorageInterface<TestCase> $storageTwo */
        $storageTwo = $this->createMock(StorageInterface::class);

        $registry = new SimpleRegistry(
            [
                'foo' => $fooGateway = $this->createMock(GatewayInterface::class),
                'bar' => $barGateway = $this->createMock(GatewayInterface::class),
            ],
            [
                stdClass::class => $storageOne,
                TestCase::class => $storageTwo,
            ],
            [
                'foo' => 'fooGatewayFactory',
                'bar' => 'barGatewayFactory',
            ]
        );

        $payum = new Payum(
            $registry,
            $this->createHttpRequestVerifierMock(),
            $this->createGenericTokenFactoryMock(),
            $this->createTokenStorage()
        );

        $this->assertSame($fooGateway, $payum->getGateway('foo'));
        $this->assertSame($barGateway, $payum->getGateway('bar'));
        $this->assertSame([
            'foo' => $fooGateway,
            'bar' => $barGateway,
        ], $payum->getGateways());
    }

    public function testShouldAllowGetStoragesFromRegistryInConstructor()
    {
        $barModel = (new class {})::class;

        $fooStorage = new MockStorage(stdClass::class);
        $barStorage = new MockStorage($barModel);

        $registry = new SimpleRegistry(
            [
                'foo' => 'fooGateway',
                'bar' => 'barGateway',
            ],
            [
                stdClass::class => $fooStorage,
                $barModel => $barStorage,
            ],
            [
                'foo' => 'fooGatewayFactory',
                'bar' => 'barGatewayFactory',
            ]
        );

        $payum = new Payum(
            $registry,
            $this->createHttpRequestVerifierMock(),
            $this->createGenericTokenFactoryMock(),
            $this->createTokenStorage()
        );

        $this->assertSame($fooStorage, $payum->getStorage(stdClass::class));
        $this->assertSame($barStorage, $payum->getStorage($barModel));
        $this->assertSame([
            stdClass::class => $fooStorage,
            $barModel => $barStorage,
        ], $payum->getStorages());
    }

    public function testShouldAllowGetGatewayFactoriesFromRegistryInConstructor()
    {
        /** @var MockObject | StorageInterface<stdClass> $storageOne */
        $storageOne = $this->createMock(StorageInterface::class);

        /** @var MockObject | StorageInterface<TestCase> $storageTwo */
        $storageTwo = $this->createMock(StorageInterface::class);

        $registry = new SimpleRegistry(
            [
                'foo' => 'fooGateway',
                'bar' => 'barGateway',
            ],
            [
                stdClass::class => $storageOne,
                TestCase::class => $storageTwo,
            ],
            [
                'foo' => 'fooGatewayFactory',
                'bar' => 'barGatewayFactory',
            ]
        );

        $payum = new Payum(
            $registry,
            $this->createHttpRequestVerifierMock(),
            $this->createGenericTokenFactoryMock(),
            $this->createTokenStorage()
        );

        $this->assertSame('fooGatewayFactory', $payum->getGatewayFactory('foo'));
        $this->assertSame('barGatewayFactory', $payum->getGatewayFactory('bar'));
        $this->assertSame([
            'foo' => 'fooGatewayFactory',
            'bar' => 'barGatewayFactory',
        ], $payum->getGatewayFactories());
    }

    /**
     * @return MockObject | RegistryInterface<stdClass>
     */
    protected function createRegistryMock()
    {
        return $this->createMock(RegistryInterface::class);
    }

    /**
     * @return MockObject|HttpRequestVerifierInterface
     */
    protected function createHttpRequestVerifierMock()
    {
        return $this->createMock(HttpRequestVerifierInterface::class);
    }

    /**
     * @return MockObject|GenericTokenFactoryInterface
     */
    protected function createGenericTokenFactoryMock()
    {
        return $this->createMock(GenericTokenFactoryInterface::class);
    }

    /**
     * @return MockObject | StorageInterface<stdClass>
     */
    protected function createTokenStorage(): StorageInterface | MockObject
    {
        return $this->createMock(StorageInterface::class);
    }
}
