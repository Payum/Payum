<?php

namespace Payum\Core\Tests\Bridge\Symfony\Action\Http;

use Payum\Core\Bridge\Symfony\Action\RenderTemplateAction;
use Payum\Core\Request\Generic;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Tests\GenericActionTest;
use Symfony\Component\Templating\EngineInterface;

class RenderTemplateActionTest extends GenericActionTest
{
    /**
     * @var string
     */
    protected $requestClass = RenderTemplate::class;

    /**
     * @var string
     */
    protected $actionClass = RenderTemplateAction::class;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $templating;

    protected function setUp()
    {
        $this->templating = $this->getMock(EngineInterface::class);
        $this->action = new $this->actionClass($this->templating, 'layout.html.engine');
    }

    public function couldBeConstructedWithoutAnyArguments()
    {
        //overwrite
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldCallRenderWithCorrectArguments()
    {
        $this->templating
            ->expects($this->once())
            ->method('render')
            ->with(
                'template.html.engine',
                [
                    'layout' => 'layout.html.engine',
                    'foo' => 'bar',
                ]
            );

        $request = new $this->requestClass('template.html.engine', ['foo' => 'bar']);
        $this->action->execute($request);
    }
}
