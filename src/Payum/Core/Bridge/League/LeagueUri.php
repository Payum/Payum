<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\League;

use League\Uri\Http;
use League\Uri\Modifier;
use League\Uri\UriModifier;

/**
 * Reaches league/uri ^6.4 and ^7 through whichever spelling the installed release has. 7.0 renamed the
 * createFrom* factories and moved UriModifier's static calls onto Modifier, and later 7.x minors renamed
 * Modifier's own methods again; calling an old name on a release that deprecates it raises a notice on
 * PHP 8.4, which an application cannot act on.
 *
 * Factories are picked as callables, not called directly, so static analysis does not resolve them
 * against whichever major happens to be installed and flag the other branch as dead.
 *
 * @internal
 */
final class LeagueUri
{
    public static function http(string $uri): Http
    {
        return self::factory(Http::class, 'new', 'createFromString')($uri);
    }

    /**
     * @param array<string, mixed> $server
     */
    public static function httpFromServer(array $server): Http
    {
        return self::factory(Http::class, 'fromServer', 'createFromServer')($server);
    }

    /**
     * @param class-string $class
     */
    public static function factory(string $class, string $method, string $legacyMethod): callable
    {
        return [$class, method_exists($class, $method) ? $method : $legacyMethod];
    }

    public static function mergeQuery(Http $uri, string $query): Http
    {
        return self::modify($uri, ['mergeQuery'], $query);
    }

    public static function appendSegment(Http $uri, string $segment): Http
    {
        return self::modify($uri, ['appendPath', 'appendSegment'], $segment);
    }

    public static function replaceBasename(Http $uri, string $basename): Http
    {
        return self::modify($uri, ['replaceBasename'], $basename);
    }

    /**
     * @param non-empty-list<string> $operations the operation's names, newest first; the last is the one
     *                                           league/uri-components 2's UriModifier has
     */
    private static function modify(Http $uri, array $operations, string $argument): Http
    {
        if (! class_exists(Modifier::class)) {
            $modified = [UriModifier::class, $operations[array_key_last($operations)]]($uri, $argument);
        } else {
            $modifier = self::factory(Modifier::class, 'wrap', 'from')($uri);

            foreach ($operations as $operation) {
                if (method_exists($modifier, $operation)) {
                    $modifier = $modifier->{$operation}($argument);

                    break;
                }
            }

            $modified = method_exists($modifier, 'unwrap') ? $modifier->unwrap() : [$modifier, 'getUri']();
        }

        assert($modified instanceof Http);

        return $modified;
    }
}
