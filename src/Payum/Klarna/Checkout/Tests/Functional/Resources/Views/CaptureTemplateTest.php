<?php
namespace Payum\Klarna\Checkout\Tests\Functional\Resources\Views;

use Payum\Core\Bridge\Twig\TwigFactory;
use PHPUnit\Framework\TestCase;

class CaptureTemplateTest extends TestCase
{
    public function testShouldRenderExpectedResult()
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

        $this->assertSame($expectedResult, $actualResult);
    }
}
