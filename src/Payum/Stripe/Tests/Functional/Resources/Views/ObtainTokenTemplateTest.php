<?php
namespace Payum\Klarna\Checkout\Tests\Functional\Resources\Views;

use Payum\Core\Bridge\Twig\TwigFactory;

class ObtainTokenTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldRenderObtainJsTokenTemplate()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_js_token.html.twig', array(
            'publishable_key' => 'theKey',
        ));

        $this->assertContains('Stripe.setPublishableKey("theKey");', $result);
    }

    /**
     * @test
     */
    public function shouldRenderObtainCheckoutTokenTemplate()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_checkout_token.html.twig', array(
            'publishable_key' => 'theKey',
        ));

        $this->assertContains('data-key="theKey"', $result);
        $this->assertContains('https://checkout.stripe.com/checkout.js', $result);
    }

    /**
     * @test
     */
    public function shouldRenderCheckoutTokenWithCurrencySet()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_checkout_token.html.twig', array(
            'model' => array('currency' => 'GBP'),
        ));

        $this->assertContains('data-currency="GBP"', $result);
    }

    /**
     * @test
     */
    public function shouldRenderCheckoutTokenWithDollarsIfNoCurrencySet()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_checkout_token.html.twig', array(
            'model' => array('currency' => ''),
        ));

        $this->assertContains('data-currency="USD"', $result);
    }
}
