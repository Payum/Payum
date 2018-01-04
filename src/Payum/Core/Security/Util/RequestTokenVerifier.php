<?php

namespace Payum\Core\Security\Util;

use League\Uri\Http;
use League\Uri\Modifiers\Normalize;

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
        $modifier = new Normalize();

        $newUri = $modifier->process($uri);
        $newAltUri = $modifier->process($altUri);

        return $newUri->getPath() === $newAltUri->getPath();
    }
}
