<?php
namespace Payum\Core\Tests;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\GatewayInterface;
use Payum\Core\HttpClientInterface;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;

class PayumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementRegistryInterface()
    {
        $rc = new \ReflectionClass(Payum::class);

        $this->assertTrue($rc->implementsInterface(RegistryInterface::class));
    }

    /**
     * @test
     */
    public function shouldBeConstructedWithExpectedArguments()
    {
        new Payum(
            $this->createRegistryMock(),
            $this->createHttpRequestVerifierMock(),
            $this->createGenericTokenFactoryMock(),
            $this->createTokenStorage()
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetHttpRequestVerifierSetInConstructor()
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

    /**
     * @test
     */
    public function shouldAllowGetGenericTokenFactorySetInConstructor()
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

    /**
     * @test
     */
    public function shouldAllowGetTokenStorageSetInConstructor()
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

    /**
     * @test
     */
    public function shouldAllowGetGatewayFromRegistryInConstructor()
    {
        $registry = new SimpleRegistry(
            [
                'foo' => $fooGateway = $this->getMock(GatewayInterface::class),
                'bar' => $barGateway = $this->getMock(GatewayInterface::class),
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

    /**
     * @test
     */
    public function shouldAllowGetStoragesFromRegistryInConstructor()
    {
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

        $this->assertSame('fooStorage', $payum->getStorage('foo'));
        $this->assertSame('barStorage', $payum->getStorage('bar'));
        $this->assertSame([
            'foo' => 'fooStorage',
            'bar' => 'barStorage',
        ], $payum->getStorages());
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayFactoriesFromRegistryInConstructor()
    {
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
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    protected function createRegistryMock()
    {
        return $this->getMock(RegistryInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpRequestVerifierInterface
     */
    protected function createHttpRequestVerifierMock()
    {
        return $this->getMock(HttpRequestVerifierInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GenericTokenFactoryInterface
     */
    protected function createGenericTokenFactoryMock()
    {
        return $this->getMock(GenericTokenFactoryInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createTokenStorage()
    {
        return $this->getMock(StorageInterface::class);
    }
}
