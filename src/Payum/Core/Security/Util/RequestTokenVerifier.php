<?php

namespace Payum\Core\Security\Util;

use League\Uri\Components\Path;
use League\Uri\Http;

class RequestTokenVerifier
{
    public static function isValid(string $requestUri, string $tokenUri): bool
    {
        $uri = Http::createFromString($requestUri);
        $altUri = Http::createFromString($tokenUri);

        $uriPath = Path::createFromUri($uri);
        $altUriPath = Path::createFromUri($altUri);

        return rawurldecode((string) $uriPath) === rawurldecode((string) $altUriPath);
    }
}
