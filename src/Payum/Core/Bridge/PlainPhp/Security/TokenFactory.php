<?php

namespace Payum\Core\Bridge\PlainPhp\Security;

use League\Uri\Components\HierarchicalPath;
use League\Uri\Components\Path;
use League\Uri\Http as HttpUri;
use League\Uri\UriModifier;
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
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, string $baseUrl = null)
    {
        parent::__construct($tokenStorage, $storageRegistry);

        $this->baseUrl = $baseUrl ? HttpUri::createFromString($baseUrl) : HttpUri::createFromServer($_SERVER);
    }

    protected function generateUrl(string $path, array $parameters = []): string
    {
        $hierarchicalPath = HierarchicalPath::createFromUri($this->baseUrl);
        if ('php' === pathinfo($hierarchicalPath->getBasename(), PATHINFO_EXTENSION)) {
            $newPath = UriModifier::replaceBasename($this->baseUrl, (new Path($path))->withoutLeadingSlash())->getPath();
        } else {
            $newPath = UriModifier::appendSegment($this->baseUrl, $path)->getPath();
        }

        $uri = $this->baseUrl->withPath($newPath);
        $uri = $this->addQueryToUri($uri, $parameters);

        return (string) $uri;
    }
}
