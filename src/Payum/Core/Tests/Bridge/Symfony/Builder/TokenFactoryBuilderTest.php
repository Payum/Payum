<?php
namespace Payum\Core\Tests\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Builder\TokenFactoryBuilder;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenFactoryBuilderTest extends TestCase
{
    public function testShouldBuildSymfonyHttpRequestVerifier()
    {
        /** @var StorageInterface $tokenStorage */
        $tokenStorage = $this->createMock(StorageInterface::class);

        /** @var StorageRegistryInterface $storageRegistry */
        $storageRegistry = $this->createMock(StorageRegistryInterface::class);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $builder = new TokenFactoryBuilder($urlGenerator);

        $tokenFactory = $builder->build($tokenStorage, $storageRegistry);

        $this->assertInstanceOf(TokenFactory::class, $tokenFactory);
    }

    public function testAllowUseBuilderAsAsFunction()
    {
        /** @var StorageInterface $tokenStorage */
        $tokenStorage = $this->createMock(StorageInterface::class);

        /** @var StorageRegistryInterface $storageRegistry */
        $storageRegistry = $this->createMock(StorageRegistryInterface::class);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $builder = new TokenFactoryBuilder($urlGenerator);

        $tokenFactory = $builder($tokenStorage, $storageRegistry);

        $this->assertInstanceOf(TokenFactory::class, $tokenFactory);
    }
}
