<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Action;

use Iterator;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\RenderTemplateAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Template\RendererInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

final class RenderTemplateActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(RenderTemplateAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportRenderTemplate(): void
    {
        $action = new RenderTemplateAction($this->createMock(RendererInterface::class));

        $this->assertTrue($action->supports(new RenderTemplate('aTemplate')));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testShouldNotSupportAnythingElse(mixed $request): void
    {
        $action = new RenderTemplateAction($this->createMock(RendererInterface::class));

        $this->assertFalse($action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testThrowIfNotSupportedRequestPassedToExecute(mixed $request): void
    {
        $action = new RenderTemplateAction($this->createMock(RendererInterface::class));

        $this->expectException(RequestNotSupportedException::class);

        $action->execute($request);
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [new stdClass()];
    }

    public function testShouldSetWhatTheRendererReturnedAsTheResult(): void
    {
        $renderer = $this->createMock(RendererInterface::class);
        $renderer
            ->expects($this->once())
            ->method('render')
            ->with('@PayumCore/theTemplate.html.twig', [
                'foo' => 'fooVal',
            ])
            ->willReturn('theRenderedView')
        ;

        $action = new RenderTemplateAction($renderer);

        $action->execute($request = new RenderTemplate('@PayumCore/theTemplate.html.twig', [
            'foo' => 'fooVal',
        ]));

        $this->assertSame('theRenderedView', $request->getResult());
    }
}
