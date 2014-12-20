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
    public function shouldGuessCorrectCorePathByPaymentClass()
    {
        $path = TwigFactory::guessViewsPath('Payum\Core\Payment');

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
}
