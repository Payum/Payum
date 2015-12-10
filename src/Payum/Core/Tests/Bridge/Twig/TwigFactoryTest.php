<?php
namespace Payum\Core\Bridge\Twig;

class TwigFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldAllowCreateATwigEnvironment()
    {
        $twig = TwigFactory::createGeneric();

        $this->assertInstanceOf('Twig_Environment', $twig);
    }

    /**
     * @test
     */
    public function shouldGuessCorrectCorePathByGatewayClass()
    {
        $path = TwigFactory::guessViewsPath('Payum\Core\Gateway');

        $this->assertFileExists($path);
        $this->assertStringEndsWith('Payum/Core/Resources/views', $path);
    }

    /**
     * @test
     */
    public function shouldNotGuessPathIfFileNotExist()
    {
        $this->assertNull(TwigFactory::guessViewsPath('Foo\Bar\Baz'));
    }

    /**
     * @test
     */
    public function shouldAllowCreateGenericPaths()
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
