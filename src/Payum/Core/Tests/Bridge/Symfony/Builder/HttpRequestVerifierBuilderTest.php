<?php
namespace Payum\Core\Tests\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Builder\HttpRequestVerifierBuilder;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Storage\StorageInterface;

class HttpRequestVerifierBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new HttpRequestVerifierBuilder();
    }

    public function testShouldBuildSymfonyHttpRequestVerifier()
    {
        /** @var StorageInterface $tokenStorage */
        $tokenStorage = $this->getMock(StorageInterface::class);

        $builder = new HttpRequestVerifierBuilder();

        $verifier = $builder->build($tokenStorage);

        $this->assertInstanceOf(HttpRequestVerifier::class, $verifier);
        $this->assertAttributeSame($tokenStorage, 'tokenStorage', $verifier);
    }

    public function testAllowUseBuilderAsAsFunction()
    {
        /** @var StorageInterface $tokenStorage */
        $tokenStorage = $this->getMock(StorageInterface::class);

        $builder = new HttpRequestVerifierBuilder();

        $verifier = $builder($tokenStorage);

        $this->assertInstanceOf(HttpRequestVerifier::class, $verifier);
    }
}
