<?php

namespace Payum\Core\Tests\Bridge\Twig;

use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Gateway;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class TwigFactoryTest extends TestCase
{
    public function testShouldAllowCreateATwigEnvironment(): void
    {
        $twig = TwigFactory::createGeneric();

        $this->assertInstanceOf(Environment::class, $twig);
    }

    public function testShouldGuessCorrectCorePathByGatewayClass(): void
    {
        $path = TwigFactory::guessViewsPath(Gateway::class);

        $this->assertFileExists($path);
        $this->assertStringEndsWith('Payum/Core/Resources/views', $path);
    }

    public function testShouldNotGuessPathIfFileNotExist(): void
    {
        $this->assertNull(TwigFactory::guessViewsPath('Foo\Bar\Baz'));
    }

    public function testShouldAllowCreateGenericPaths(): void
    {
        $paths = TwigFactory::createGenericPaths();

        $paths = array_flip($paths);

        $this->assertArrayHasKey('PayumCore', $paths);
        $this->assertStringEndsWith('Payum/Core/Resources/views', $paths['PayumCore']);

        $this->assertArrayHasKey('PayumKlarnaCheckout', $paths);
        $this->assertStringEndsWith('Payum/Klarna/Checkout/Resources/views', $paths['PayumKlarnaCheckout']);

        $this->assertArrayHasKey('PayumStripe', $paths);
        $this->assertStringEndsWith('Payum/Stripe/Resources/views', $paths['PayumStripe']);

        $this->assertArrayHasKey('PayumSymfonyBridge', $paths);
        $this->assertStringEndsWith('Payum/Core/Bridge/Symfony/Resources/views', $paths['PayumSymfonyBridge']);
    }
}
