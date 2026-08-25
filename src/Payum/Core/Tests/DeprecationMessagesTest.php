<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function class_exists;
use function dirname;
use function enum_exists;
use function in_array;
use function interface_exists;
use function ksort;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function strlen;
use function substr;
use function trait_exists;
use function trim;

/**
 * A deprecation that names no replacement, or that claims a removal which has already happened, is worse
 * than no deprecation at all: it costs the reader a lookup and gives them nothing to act on. Both kinds
 * were shipped in 1.x and survived into 2.0, so the rules are pinned here rather than left to review.
 */
final class DeprecationMessagesTest extends TestCase
{
    /**
     * @dataProvider provideDeprecations
     */
    public function testShouldSayWhenTheDeprecatedThingGoes(string $where, string $message): void
    {
        $this->assertStringContainsString(
            'in 3.0',
            $message,
            sprintf('%s does not say that it goes in 3.0: "%s"', $where, $message)
        );
    }

    /**
     * @dataProvider provideDeprecations
     */
    public function testShouldSayWhatToUseInstead(string $where, string $message): void
    {
        $forwardPath = trim((string) preg_replace('#^since [^.]+\.#', '', $message));

        $this->assertNotSame(
            '',
            $forwardPath,
            sprintf('%s says only that it is deprecated, and nothing about what to use instead', $where)
        );
    }

    /**
     * @dataProvider provideDeprecations
     */
    public function testShouldOnlyNamePayumClassesThatExist(string $where, string $message): void
    {
        preg_match_all('#Payum\\\\Core(?:\\\\[A-Za-z_][A-Za-z0-9_]*)+#', $message, $matches);

        foreach ($matches[0] as $class) {
            $this->assertTrue(
                class_exists($class) || interface_exists($class) || trait_exists($class) || enum_exists($class),
                sprintf('%s points at %s, which does not exist', $where, $class)
            );
        }

        $this->addToAssertionCount(1);
    }

    /**
     * Every `@deprecated` docblock in the Payum\Core sources, keyed by the path it sits at relative to
     * Core and the line it starts on.
     *
     * The key has to be the whole relative path, not the file name: Core has several same-named classes
     * across its bridges, and two of them carrying a deprecation on the same line is enough for PHPUnit
     * to reject the whole data set as ambiguous.
     *
     * Scoped to Core because the replacements a gateway package names live in that package, and several
     * of those are `$this->api` rather than a class — nothing this test can resolve.
     *
     * @return array<string, array{string, string}>
     */
    public static function provideDeprecations(): array
    {
        $root = dirname(__DIR__);

        $files = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
                static fn (SplFileInfo $file): bool => ! $file->isDir()
                    || ! in_array($file->getFilename(), ['Tests', 'Resources'], true)
            )
        );

        $found = [];

        foreach ($files as $file) {
            /** @var SplFileInfo $file */
            if ('php' !== $file->getExtension()) {
                continue;
            }

            $path = $file->getPathname();
            $relative = substr($path, strlen($root) + 1);

            foreach (self::readDeprecations((string) file_get_contents($path)) as $line => $message) {
                $found[sprintf('%s:%d', $relative, $line)] = [
                    sprintf('%s:%d', $path, $line),
                    $message,
                ];
            }
        }

        ksort($found);

        return $found;
    }

    /**
     * The text of each `@deprecated` docblock tag, keyed by the line it starts on.
     *
     * A tag runs until the next tag or the end of its docblock, so a wrapped message is read whole —
     * which matters, since the replacement is usually what got wrapped onto the second line.
     *
     * @return array<int, string>
     */
    private static function readDeprecations(string $source): array
    {
        $found = [];

        if (! preg_match_all('#/\*\*.*?\*/#s', $source, $blocks, PREG_OFFSET_CAPTURE)) {
            return $found;
        }

        foreach ($blocks[0] as [$block, $offset]) {
            $startLine = 1 + substr_count($source, "\n", 0, $offset);

            $lines = explode("\n", $block);
            $collecting = null;

            foreach ($lines as $index => $rawLine) {
                $line = trim((string) preg_replace('#^\s*(/\*\*|\*/|\*)\s?#', '', $rawLine));

                if (str_starts_with($line, '@deprecated')) {
                    $collecting = $startLine + $index;
                    $found[$collecting] = trim(substr($line, 11));

                    continue;
                }

                if (null === $collecting) {
                    continue;
                }

                if (str_starts_with($line, '@') || '' === $line) {
                    $collecting = null;

                    continue;
                }

                $found[$collecting] = trim($found[$collecting] . ' ' . $line);
            }
        }

        return $found;
    }
}
