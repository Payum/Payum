<?php

namespace Payum\Core\Security\Util;

use League\Uri\Components\Path;
use Payum\Core\Bridge\League\LeagueUri;

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
        $pathFromUri = LeagueUri::factory(Path::class, 'fromUri', 'createFromUri');

        $uriPath = $pathFromUri(LeagueUri::http($requestUri));
        $altUriPath = $pathFromUri(LeagueUri::http($tokenUri));

        return rawurldecode((string) $uriPath) === rawurldecode((string) $altUriPath);
    }
}
