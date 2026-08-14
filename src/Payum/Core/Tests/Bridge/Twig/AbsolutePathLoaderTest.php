<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\Twig;

use Payum\Core\Bridge\Twig\AbsolutePathLoader;
use PHPUnit\Framework\TestCase;
use Twig\Loader\LoaderInterface;

final class AbsolutePathLoaderTest extends TestCase
{
    public function testShouldImplementTwigLoaderInterface(): void
    {
        $this->assertInstanceOf(LoaderInterface::class, new AbsolutePathLoader());
    }

    public function testShouldReadTheFileAtTheGivenPath(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';

        $source = (new AbsolutePathLoader())->getSourceContext($file);

        $this->assertStringContainsString('<!DOCTYPE html>', $source->getCode());
        $this->assertSame($file, $source->getName());
    }

    public function testShouldOnlyClaimPathsThatExist(): void
    {
        $loader = new AbsolutePathLoader();

        $this->assertTrue($loader->exists(__DIR__ . '/../../../Resources/views/layout.html.twig'));
        $this->assertFalse($loader->exists('@PayumCore/layout.html.twig'));
        $this->assertFalse($loader->exists('/nope/missing.html.twig'));
    }

    public function testShouldKeyTheCacheOnThePath(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';

        $this->assertSame($file, (new AbsolutePathLoader())->getCacheKey($file));
    }

    public function testShouldReportFreshnessAgainstTheFileMtime(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';
        $loader = new AbsolutePathLoader();

        $this->assertTrue($loader->isFresh($file, filemtime($file) + 10));
        $this->assertFalse($loader->isFresh($file, filemtime($file) - 10));
    }
}
