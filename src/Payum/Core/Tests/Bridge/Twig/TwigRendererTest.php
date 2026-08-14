<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\Twig;

use Payum\Core\Bridge\Twig\AbsolutePathLoader;
use Payum\Core\Bridge\Twig\TwigRenderer;
use Payum\Core\Template\RendererInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;

final class TwigRendererTest extends TestCase
{
    public function testShouldImplementRendererInterface(): void
    {
        $renderer = new TwigRenderer($this->twig(), '@PayumCore/layout.html.twig');

        $this->assertInstanceOf(RendererInterface::class, $renderer);
    }

    public function testShouldRenderATemplateWithItsContext(): void
    {
        $renderer = new TwigRenderer($this->twig([
            'form.html.twig' => 'Pay {{ amount }}',
        ]), '@PayumCore/layout.html.twig');

        $this->assertSame('Pay 123', $renderer->render('form.html.twig', [
            'amount' => 123,
        ]));
    }

    public function testShouldMakeTheLayoutAvailableToTheTemplate(): void
    {
        $renderer = new TwigRenderer($this->twig([
            'form.html.twig' => '{{ layout }}',
        ]), '@PayumCore/layout.html.twig');

        $this->assertSame('@PayumCore/layout.html.twig', $renderer->render('form.html.twig'));
    }

    public function testShouldLetTheContextOverrideTheLayout(): void
    {
        $renderer = new TwigRenderer($this->twig([
            'form.html.twig' => '{{ layout }}',
        ]), '@PayumCore/layout.html.twig');

        $this->assertSame('@Acme/layout.html.twig', $renderer->render('form.html.twig', [
            'layout' => '@Acme/layout.html.twig',
        ]));
    }

    public function testShouldRegisterThePathsItWasGiven(): void
    {
        $renderer = new TwigRenderer(
            $this->twig(),
            '@PayumCore/layout.html.twig',
            [
                'PayumCore' => __DIR__ . '/../../../Resources/views',
            ],
        );

        $this->assertStringContainsString(
            '<!DOCTYPE html>',
            $renderer->render('@PayumCore/layout.html.twig'),
        );
    }

    public function testShouldRenderATemplateGivenAsAnAbsolutePath(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';

        $renderer = new TwigRenderer($this->twig(), '@PayumCore/layout.html.twig', [
            'PayumCore' => __DIR__ . '/../../../Resources/views',
        ], [$file]);

        $this->assertStringContainsString('<!DOCTYPE html>', $renderer->render($file));
    }

    public function testShouldStillRenderANamespacedName(): void
    {
        $renderer = new TwigRenderer($this->twig(), '@PayumCore/layout.html.twig', [
            'PayumCore' => __DIR__ . '/../../../Resources/views',
        ]);

        $this->assertStringContainsString('<!DOCTYPE html>', $renderer->render('@PayumCore/layout.html.twig'));
    }

    public function testShouldRegisterTheAbsolutePathLoaderOnlyOnce(): void
    {
        $twig = $this->twig();
        $paths = [
            'PayumCore' => __DIR__ . '/../../../Resources/views',
        ];
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';

        new TwigRenderer($twig, '@PayumCore/layout.html.twig', $paths, [$file]);
        $renderer = new TwigRenderer($twig, '@PayumCore/layout.html.twig', $paths, [$file]);

        /** @var ChainLoader $loader */
        $loader = $twig->getLoader();
        $absolutePathLoaders = array_filter(
            $loader->getLoaders(),
            static fn ($registered): bool => $registered instanceof AbsolutePathLoader,
        );

        $this->assertCount(1, $absolutePathLoaders);
        $this->assertStringContainsString('<!DOCTYPE html>', $renderer->render($file));
    }

    public function testShouldSearchEveryDirectoryRegisteredUnderANamespace(): void
    {
        $renderer = new TwigRenderer($this->twig(), '@PayumCore/layout.html.twig', [
            'PayumCore' => [
                __DIR__ . '/../../Resources/views',
                __DIR__ . '/../../Resources/views/shared',
            ],
        ]);

        $this->assertStringContainsString('overridden', $renderer->render('@PayumCore/override.html.twig'));
        $this->assertStringContainsString('shared', $renderer->render('@PayumCore/shared_only.html.twig'));
    }

    public function testShouldRenderTheFragmentLayoutWithoutAPageWrapper(): void
    {
        $renderer = new TwigRenderer($this->twig(), '@PayumCore/fragment.html.twig', [
            'PayumCore' => __DIR__ . '/../../../Resources/views',
        ]);

        $output = $renderer->render('@PayumCore/fragment.html.twig');

        $this->assertStringNotContainsString('<!DOCTYPE html>', $output);
        $this->assertStringNotContainsString('<html>', $output);
        $this->assertStringNotContainsString('<body>', $output);
    }

    /**
     * @param array<string, string> $templates
     */
    private function twig(array $templates = []): Environment
    {
        return new Environment(new ChainLoader([new ArrayLoader($templates)]));
    }
}
