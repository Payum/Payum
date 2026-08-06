<?php

namespace Payum\Core\Security\Util;

use League\Uri\Components\Path;
use League\Uri\Http as HttpUri;

class RequestTokenVerifier
{
    /**
     * @param string $requestUri
     * @param string $tokenUri
     *
     * @return bool
     */
    public static function isValid($requestUri, $tokenUri)
    {
        $newUri = self::uriFactory(HttpUri::class, 'new', 'createFromString');
        $pathFromUri = self::uriFactory(Path::class, 'fromUri', 'createFromUri');

        $uriPath = $pathFromUri($newUri($requestUri));
        $altUriPath = $pathFromUri($newUri($tokenUri));

        return rawurldecode((string) $uriPath) === rawurldecode((string) $altUriPath);
    }

    /**
     * league/uri 7 renamed the createFrom* factories to named constructors and made
     * the originals private, so both spellings have to be reachable while ^6.4 and
     * ^7.0 are supported. The factory is returned as a callable rather than called
     * directly so that static analysis does not resolve it against whichever major
     * happens to be installed and flag the other branch as dead.
     *
     * @param class-string $class
     */
    private static function uriFactory(string $class, string $method, string $legacyMethod): callable
    {
        return [$class, method_exists($class, $method) ? $method : $legacyMethod];
    }
}
