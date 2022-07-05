<?php

namespace Payum\Core\Tests\Bridge\Twig\Action;

use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class RenderTemplateActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Bridge\Twig\Action\RenderTemplateAction::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Action\ActionInterface::class));
    }

    public function testShouldSupportRenderTemplate()
    {
        $action = new RenderTemplateAction($this->createTwigMock(), 'aLayout');

        $this->assertTrue($action->supports(new RenderTemplate('aTemplate', [])));
    }

    public function testShouldNotSupportAnythingNotRenderTemplate()
    {
        $action = new RenderTemplateAction($this->createTwigMock(), 'aLayout');

        $this->assertFalse($action->supports('foo'));
    }

    public function testThrowIfNotSupportedRequestPassedToExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action RenderTemplateAction is not supported the request string.');
        $action = new RenderTemplateAction($this->createTwigMock(), 'aLayout');

        $action->execute('foo');
    }

    public function testShouldRenderExpectedTemplateAndContext()
    {
        $expectedTemplate = 'theTemplate';

        $expectedView = 'theView';

        $context = $expectedContext = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];
        $expectedContext['layout'] = 'theLayout';

        $twigMock = $this->createTwigMock();
        $twigMock
            ->expects($this->once())
            ->method('render')
            ->with($expectedTemplate, $expectedContext)
            ->willReturn($expectedView)
        ;

        $action = new RenderTemplateAction($twigMock, 'theLayout');

        $renderTemplate = new RenderTemplate($expectedTemplate, $context);
        $action->execute($renderTemplate);

        $this->assertSame($expectedView, $renderTemplate->getResult());
    }

    public function testShouldRenderExpectedTemplateAndContextWithCustomLayout()
    {
        $expectedTemplate = 'theTemplate';

        $expectedView = 'theView';

        $context = $expectedContext = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'layout' => 'theCustomLayout',
        ];
        $expectedContext['layout'] = 'theCustomLayout';

        $twigMock = $this->createTwigMock();
        $twigMock
            ->expects($this->once())
            ->method('render')
            ->with($expectedTemplate, $expectedContext)
            ->willReturn($expectedView)
        ;

        $action = new RenderTemplateAction($twigMock, 'defaultLayout');

        $renderTemplate = new RenderTemplate($expectedTemplate, $context);
        $action->execute($renderTemplate);

        $this->assertSame($expectedView, $renderTemplate->getResult());
    }

    /**
     * @return MockObject|Environment
     */
    protected function createTwigMock()
    {
        return $this->createMock(Environment::class, ['render'], [], '', false);
    }
}
