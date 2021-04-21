<?php
namespace Payum\Core\Tests\Bridge\Twig\Action;

use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class RenderTemplateActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Twig\Action\RenderTemplateAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTwigAndLayoutAsArguments()
    {
        new RenderTemplateAction($this->createTwigMock(), 'aLayout');
    }

    /**
     * @test
     */
    public function shouldSupportRenderTemplate()
    {
        $action = new RenderTemplateAction($this->createTwigMock(), 'aLayout');

        $this->assertTrue($action->supports(new RenderTemplate('aTemplate', array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotRenderTemplate()
    {
        $action = new RenderTemplateAction($this->createTwigMock(), 'aLayout');

        $this->assertFalse($action->supports('foo'));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestPassedToExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action RenderTemplateAction is not supported the request string.');
        $action = new RenderTemplateAction($this->createTwigMock(), 'aLayout');

        $action->execute('foo');
    }

    /**
     * @test
     */
    public function shouldRenderExpectedTemplateAndContext()
    {
        $expectedTemplate = 'theTemplate';

        $expectedView = 'theView';

        $context = $expectedContext = array('foo' => 'fooVal', 'bar' => 'barVal');
        $expectedContext['layout'] = 'theLayout';

        $twigMock = $this->createTwigMock();
        $twigMock
            ->expects($this->once())
            ->method('render')
            ->with($expectedTemplate, $expectedContext)
            ->will($this->returnValue($expectedView))
        ;

        $action = new RenderTemplateAction($twigMock, 'theLayout');

        $renderTemplate = new RenderTemplate($expectedTemplate, $context);
        $action->execute($renderTemplate);

        $this->assertEquals($expectedView, $renderTemplate->getResult());
    }

    /**
     * @test
     */
    public function shouldRenderExpectedTemplateAndContextWithCustomLayout()
    {
        $expectedTemplate = 'theTemplate';

        $expectedView = 'theView';

        $context = $expectedContext = array('foo' => 'fooVal', 'bar' => 'barVal', 'layout' => 'theCustomLayout');
        $expectedContext['layout'] = 'theCustomLayout';

        $twigMock = $this->createTwigMock();
        $twigMock
            ->expects($this->once())
            ->method('render')
            ->with($expectedTemplate, $expectedContext)
            ->will($this->returnValue($expectedView))
        ;

        $action = new RenderTemplateAction($twigMock, 'defaultLayout');

        $renderTemplate = new RenderTemplate($expectedTemplate, $context);
        $action->execute($renderTemplate);

        $this->assertEquals($expectedView, $renderTemplate->getResult());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Environment
     */
    protected function createTwigMock()
    {
        return $this->createMock(Environment::class, array('render'), array(), '', false);
    }
}
