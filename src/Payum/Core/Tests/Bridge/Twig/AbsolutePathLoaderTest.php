<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\Twig;

use Payum\Core\Bridge\Twig\AbsolutePathLoader;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;
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

    public function testShouldThrowWhenTheFileExistsButCannotBeRead(): void
    {
        if (function_exists('posix_getuid') && 0 === posix_getuid()) {
            $this->markTestSkipped('File permissions are not enforced for the root user.');
        }

        $file = tempnam(sys_get_temp_dir(), 'payum-twig-unreadable-');
        chmod($file, 0000);

        try {
            $this->expectException(LoaderError::class);
            $this->expectExceptionMessage(sprintf('Template "%s" could not be read.', $file));

            (new AbsolutePathLoader())->getSourceContext($file);
        } finally {
            chmod($file, 0644);
            unlink($file);
        }
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
