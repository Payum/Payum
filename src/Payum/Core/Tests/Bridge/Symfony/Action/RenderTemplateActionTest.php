<?php

namespace Payum\Core\Tests\Bridge\Symfony\Action;

use Payum\Core\Bridge\Symfony\Action\RenderTemplateAction;
use Payum\Core\Request\Generic;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var MockObject
     */
    private $templating;

    protected function setUp(): void
    {
        $this->templating = $this->createMock(EngineInterface::class);
        $this->action = new $this->actionClass($this->templating, 'layout.html.engine');
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new \stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    public function testShouldCallRenderWithCorrectArguments()
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
