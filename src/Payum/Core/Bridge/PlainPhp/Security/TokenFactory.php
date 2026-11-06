<?php

namespace Payum\Core\Bridge\PlainPhp\Security;

use League\Uri\Components\HierarchicalPath;
use League\Uri\Contracts\UriException;
use League\Uri\Http as HttpUri;
use Payum\Core\Bridge\League\LeagueUri;
use Payum\Core\Exception\LogicException;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;

class TokenFactory extends AbstractTokenFactory
{
    protected HttpUri $baseUrl;

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, ?string $baseUrl = null)
    {
        parent::__construct($tokenStorage, $storageRegistry);

        if ($baseUrl) {
            $this->baseUrl = LeagueUri::http($baseUrl);
        }
    }

    protected function generateUrl(string $path, array $parameters = []): string
    {
        $baseUrl = $this->baseUrl ?? $this->baseUrlFromCurrentRequest();

        $hierarchicalPath = LeagueUri::factory(HierarchicalPath::class, 'fromUri', 'createFromUri')($baseUrl);
        if ('php' === pathinfo($hierarchicalPath->getBasename(), PATHINFO_EXTENSION)) {
            // Passed as a plain string rather than a Path component: league/uri 7 made
            // Path::__construct private, and replaceBasename accepts either.
            $basename = preg_replace('#^/#', '', $path);

            $newPath = LeagueUri::replaceBasename($baseUrl, $basename)->getPath();
        } else {
            $newPath = LeagueUri::appendSegment($baseUrl, $path)->getPath();
        }

        $uri = $baseUrl->withPath($newPath);
        $uri = $this->addQueryToUri($uri, $parameters);

        return (string) $uri;
    }

    // Read on every call and never kept: the factory outlives a request in a worker, and a host taken from
    // one request must not end up in the URLs issued for the next.
    private function baseUrlFromCurrentRequest(): HttpUri
    {
        try {
            return LeagueUri::httpFromServer($_SERVER);
        } catch (UriException $e) {
            throw new LogicException('Cannot build a token URL: there is no current request to take the host from. Pass a base URL to the token factory with PayumBuilder::setTokenFactory(), for example new Payum\\Core\\Bridge\\PlainPhp\\Security\\TokenFactory($tokenStorage, $storageRegistry, \'https://shop.example.com\').', 0, $e);
        }
    }
}
