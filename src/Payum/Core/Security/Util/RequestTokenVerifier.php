<?php

namespace Payum\Core\Security\Util;

use League\Uri\Components\Path;
use League\Uri\Http as HttpUri;

class RequestTokenVerifier
{
    /**
     * @param string $requestUri
     * @param string $tokenUri
     * @return bool
     */
    public static function isValid($requestUri, $tokenUri)
    {
        $uri = method_exists(HttpUri::class, 'new') ?
            HttpUri::new($requestUri) : HttpUri::createFromString($requestUri);
        $altUri = method_exists(HttpUri::class, 'new') ?
            HttpUri::new($tokenUri) : HttpUri::createFromString($tokenUri);


        $uriPath = method_exists(Path::class, 'new') ?
            Path::new($uri) : Path::createFromUri($uri);
        $altUriPath = method_exists(Path::class, 'new') ?
            Path::new($altUri) : Path::createFromUri($altUri);

        return rawurldecode((string) $uriPath) === rawurldecode((string) $altUriPath);
    }
}
