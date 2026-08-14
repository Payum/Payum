<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Template;

use Payum\Core\Exception\LogicException;
use Payum\Core\Template\RendererInterface;
use Payum\Core\Template\TemplateRenderer;
use PHPUnit\Framework\TestCase;

final class TemplateRendererTest extends TestCase
{
    public function testShouldImplementRendererInterface(): void
    {
        $this->assertInstanceOf(RendererInterface::class, new TemplateRenderer([], []));
    }

    public function testShouldResolveTheKeyToItsFileAndPassItToTheRenderer(): void
    {
        $twig = $this->createMock(RendererInterface::class);
        $twig
            ->expects($this->once())
            ->method('render')
            ->with('/acme/views/checkout.html.twig', [
                'amount' => 123,
            ])
            ->willReturn('<form>pay</form>');

        $renderer = new TemplateRenderer([
            'payum.template.acme.checkout' => '/acme/views/checkout.html.twig',
        ], [
            'twig' => $twig,
        ]);

        $this->assertSame('<form>pay</form>', $renderer->render('payum.template.acme.checkout', [
            'amount' => 123,
        ]));
    }

    public function testShouldPreferTheLongestMatchingExtension(): void
    {
        $php = $this->createMock(RendererInterface::class);
        $php->expects($this->never())->method('render');

        $blade = $this->createMock(RendererInterface::class);
        $blade->expects($this->once())->method('render')->willReturn('from blade');

        $renderer = new TemplateRenderer([
            'payum.template.acme.checkout' => '/app/views/checkout.blade.php',
        ], [
            'php' => $php,
            'blade.php' => $blade,
        ]);

        $this->assertSame('from blade', $renderer->render('payum.template.acme.checkout'));
    }

    public function testShouldThrowForAnUnknownKey(): void
    {
        $renderer = new TemplateRenderer([
            'payum.template.acme.checkout' => '/acme/views/checkout.html.twig',
        ], [
            'twig' => $this->createMock(RendererInterface::class),
        ]);

        try {
            $renderer->render('payum.template.acme.missing');

            $this->fail('Expected a LogicException.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('payum.template.acme.missing', $e->getMessage());
            $this->assertStringContainsString('payum.template.acme.checkout', $e->getMessage());
        }
    }

    public function testShouldThrowWhenNoRendererHandlesTheResolvedFile(): void
    {
        $renderer = new TemplateRenderer([
            'payum.template.acme.checkout' => '/app/views/checkout.blade.php',
        ], [
            'twig' => $this->createMock(RendererInterface::class),
        ]);

        try {
            $renderer->render('payum.template.acme.checkout');

            $this->fail('Expected a LogicException.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('blade.php', $e->getMessage());
            $this->assertStringContainsString('/app/views/checkout.blade.php', $e->getMessage());
        }
    }
}
