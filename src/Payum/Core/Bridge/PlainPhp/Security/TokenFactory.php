<?php
namespace Payum\Core\Bridge\PlainPhp\Security;

use League\Uri\Schemes\Http as HttpUri;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Storage\StorageInterface;

class TokenFactory extends AbstractTokenFactory
{
    /**
     * @var HttpUri
     */
    protected $baseUrl;

    /**
     * @param StorageInterface         $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string                   $baseUrl
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $baseUrl = null)
    {
        parent::__construct($tokenStorage, $storageRegistry);

        $this->baseUrl = $baseUrl ? HttpUri::createFromString($baseUrl) : HttpUri::createFromServer($_SERVER);
    }

    /**
     * {@inheritDoc}
     */
    protected function generateUrl($path, array $parameters = [])
    {
        $newPath = $this->baseUrl->path->withTrailingSlash().$path;

        $uri = $this->baseUrl->withPath($newPath);
        $uri = $this->addQueryToUri($uri, $parameters);

        return (string) $uri;
    }
}
