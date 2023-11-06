<?php
namespace Payum\Stripe\Tests\Functional\Resources\Views;

use Payum\Core\Bridge\Twig\TwigFactory;
use PHPUnit\Framework\TestCase;

class ObtainTokenTemplateTest extends TestCase
{
    public function testShouldRenderObtainJsTokenTemplate()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_js_token.html.twig', array(
            'publishable_key' => 'theKey',
        ));

        $this->assertStringContainsString('Stripe.setPublishableKey("theKey");', $result);
    }

    public function testShouldRenderObtainCheckoutTokenTemplate()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_checkout_token.html.twig', array(
            'publishable_key' => 'theKey',
        ));

        $this->assertStringContainsString('data-key="theKey"', $result);
        $this->assertStringContainsString('https://checkout.stripe.com/checkout.js', $result);
    }

    public function testShouldRenderCheckoutTokenWithCurrencySet()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_checkout_token.html.twig', array(
            'model' => array('currency' => 'GBP'),
        ));

        $this->assertStringContainsString('data-currency="GBP"', $result);
    }

    public function testShouldRenderCheckoutTokenWithDollarsIfNoCurrencySet()
    {
        $twig = TwigFactory::createGeneric();

        $result = $twig->render('@PayumStripe/Action/obtain_checkout_token.html.twig', array(
            'model' => array('currency' => ''),
        ));

        $this->assertStringContainsString('data-currency="USD"', $result);
    }
}
