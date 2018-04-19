<?php
namespace Payum\Klarna\Checkout\Tests\Functional\Resources\Views;

use Payum\Core\Bridge\Twig\TwigFactory;
use PHPUnit\Framework\TestCase;

class CaptureTemplateTest extends TestCase
{
    /**
     * @test
     */
    public function shouldRenderExpectedResult()
    {
        $twig = TwigFactory::createGeneric();

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
