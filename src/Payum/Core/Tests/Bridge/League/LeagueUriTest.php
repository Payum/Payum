<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\League;

use League\Uri\Components\Query;
use Payum\Core\Bridge\League\LeagueUri;
use PHPUnit\Framework\TestCase;

final class LeagueUriTest extends TestCase
{
    public function testShouldBuildAndModifyUrisWithoutRaisingDeprecations(): void
    {
        $deprecations = [];
        // league/uri raises its notices through #[\Deprecated], which reports the caller's file, so a
        // notice from inside vendor/ is league calling itself and not something Payum can fix.
        set_error_handler(static function (int $errno, string $errstr, string $errfile) use (&$deprecations): bool {
            if (! str_contains($errfile, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
                $deprecations[] = $errstr;
            }

            return true;
        }, E_DEPRECATED | E_USER_DEPRECATED);

        try {
            $uri = LeagueUri::http('https://shop.test/pay/capture.php?a=1');

            $this->assertSame('https://shop.test/pay/capture.php?a=1&cancelled=1', (string) LeagueUri::mergeQuery($uri, 'cancelled=1'));
            $this->assertSame('https://shop.test/pay/done.php?a=1', (string) LeagueUri::replaceBasename($uri, 'done.php'));
            $this->assertSame('https://shop.test/pay/done', (string) LeagueUri::appendSegment(LeagueUri::http('https://shop.test/pay'), 'done'));
            $this->assertSame('https://shop.test/x', (string) LeagueUri::httpFromServer([
                'HTTPS' => 'on',
                'HTTP_HOST' => 'shop.test',
                'SERVER_PORT' => 443,
                'REQUEST_URI' => '/x',
            ]));
            $this->assertSame('b=2', (string) LeagueUri::factory(Query::class, 'fromPairs', 'createFromPairs')([['b', '2']]));
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $deprecations);
    }
}
