<?php
namespace Payum\Core\Tests\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Builder\TokenFactoryBuilder;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenFactoryBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithUrlGeneratorAsFirstArgument()
    {
        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->getMock(UrlGeneratorInterface::class);

        new TokenFactoryBuilder($urlGenerator);
    }

    public function testShouldBuildSymfonyHttpRequestVerifier()
    {
        /** @var StorageInterface $tokenStorage */
        $tokenStorage = $this->getMock(StorageInterface::class);

        /** @var StorageRegistryInterface $storageRegistry */
        $storageRegistry = $this->getMock(StorageRegistryInterface::class);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->getMock(UrlGeneratorInterface::class);

        $builder = new TokenFactoryBuilder($urlGenerator);

        $tokenFactory = $builder->build($tokenStorage, $storageRegistry);

        $this->assertInstanceOf(TokenFactory::class, $tokenFactory);
        $this->assertAttributeSame($tokenStorage, 'tokenStorage', $tokenFactory);
        $this->assertAttributeSame($storageRegistry, 'storageRegistry', $tokenFactory);
        $this->assertAttributeSame($urlGenerator, 'urlGenerator', $tokenFactory);
    }

    public function testAllowUseBuilderAsAsFunction()
    {
        /** @var StorageInterface $tokenStorage */
        $tokenStorage = $this->getMock(StorageInterface::class);

        /** @var StorageRegistryInterface $storageRegistry */
        $storageRegistry = $this->getMock(StorageRegistryInterface::class);

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $this->getMock(UrlGeneratorInterface::class);

        $builder = new TokenFactoryBuilder($urlGenerator);

        $tokenFactory = $builder($tokenStorage, $storageRegistry);

        $this->assertInstanceOf(TokenFactory::class, $tokenFactory);
    }
}
