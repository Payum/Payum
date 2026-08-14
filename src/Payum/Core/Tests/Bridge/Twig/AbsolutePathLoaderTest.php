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
        $this->assertInstanceOf(LoaderInterface::class, new AbsolutePathLoader([]));
    }

    public function testShouldReadTheFileAtTheGivenPath(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';

        $source = (new AbsolutePathLoader([$file]))->getSourceContext($file);

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

            (new AbsolutePathLoader([$file]))->getSourceContext($file);
        } finally {
            chmod($file, 0644);
            unlink($file);
        }
    }

    public function testShouldOnlyClaimPathsThatExist(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';
        $loader = new AbsolutePathLoader([$file]);

        $this->assertTrue($loader->exists($file));
        $this->assertFalse($loader->exists('@PayumCore/layout.html.twig'));
        $this->assertFalse($loader->exists('/nope/missing.html.twig'));
    }

    public function testShouldNotClaimAnExistingFileThatIsNotInTheAllowlist(): void
    {
        $registered = __DIR__ . '/../../../Resources/views/layout.html.twig';
        $unregistered = __DIR__ . '/../../../Resources/views/fragment.html.twig';

        $loader = new AbsolutePathLoader([$registered]);

        $this->assertTrue($loader->exists($registered));
        $this->assertFalse($loader->exists($unregistered));
    }

    public function testShouldRefuseToReadAFileThatIsNotInTheAllowlist(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';
        $loader = new AbsolutePathLoader([]);

        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage(sprintf('Template "%s" is not registered.', $file));

        $loader->getSourceContext($file);
    }

    public function testShouldRefuseToReportFreshnessForAFileThatIsNotInTheAllowlist(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';
        $loader = new AbsolutePathLoader([]);

        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage(sprintf('Template "%s" is not registered.', $file));

        $loader->isFresh($file, time());
    }

    public function testShouldKeyTheCacheOnThePath(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';

        $this->assertSame($file, (new AbsolutePathLoader([$file]))->getCacheKey($file));
    }

    public function testShouldReportFreshnessAgainstTheFileMtime(): void
    {
        $file = __DIR__ . '/../../../Resources/views/layout.html.twig';
        $loader = new AbsolutePathLoader([$file]);

        $this->assertTrue($loader->isFresh($file, filemtime($file) + 10));
        $this->assertFalse($loader->isFresh($file, filemtime($file) - 10));
        $this->assertFalse($loader->isFresh($file, filemtime($file)));
    }
}
