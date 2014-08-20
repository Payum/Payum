<?php
namespace Payum\Core\Security;

use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;

class GenericTokenFactory extends AbstractGenericTokenFactory
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string $baseUrl
     * @param string $capturePath
     * @param string $notifyPath
     * @param string $authorizePath
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $baseUrl, $capturePath, $notifyPath, $authorizePath)
    {
        parent::__construct($tokenStorage, $storageRegistry, $capturePath, $notifyPath, $authorizePath);

        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateUrl($path, array $parameters = array())
    {
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($path, '/');

        if (false == empty($parameters)) {
            $url .= '?'.http_build_query($parameters);
        }

        return $url;
    }
}
