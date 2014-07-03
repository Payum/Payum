<?php
namespace Payum\Klarna\Checkout\Tests\Functional\Resources\Views;

class CaptureTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldRenderExpectedResult()
    {
        $loader = new \Twig_Loader_Filesystem;

        $rc = new \ReflectionClass('Payum\Core\Payment');
        $coreViews = dirname($rc->getFileName()).'/Resources/views';
        $loader->addPath($coreViews, 'PayumCore');

        $rc = new \ReflectionClass('Payum\Klarna\Checkout\PaymentFactory');
        $coreViews = dirname($rc->getFileName()).'/Resources/views';
        $loader->addPath($coreViews, 'PayumKlarnaCheckout');

        $twig = new \Twig_Environment($loader);

        $actualResult = $twig->render('@PayumKlarnaCheckout/Action/capture.html.twig', array(
            'snippet' => 'theSnippet',
        ));

        $expectedResult = <<<HTML
<!DOCTYPE html>
<html>
    <head>
            </head>
    <body>
            theSnippet
            </body>
</html>
HTML;

        $this->assertEquals($expectedResult, $actualResult);
    }
} 