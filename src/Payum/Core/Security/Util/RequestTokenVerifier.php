<?php

namespace Payum\Core\Security\Util;

use League\Uri\Components\Path;
use League\Uri\Http;

class RequestTokenVerifier
{
    /**
     * @param string $requestUri
     * @param string $tokenUri
     * @return bool
     */
    public static function isValid($requestUri, $tokenUri)
    {
        $uri = Http::createFromString($requestUri);
        $altUri = Http::createFromString($tokenUri);

        $uriPath = Path::createFromUri($uri);
        $altUriPath = Path::createFromUri($altUri);

        return rawurldecode((string) $uriPath) === rawurldecode((string) $altUriPath);
    }
}
