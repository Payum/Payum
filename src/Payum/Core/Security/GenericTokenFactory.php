<?php
namespace Payum\Core\Security;

use League\Url\Url;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;

class GenericTokenFactory extends AbstractGenericTokenFactory
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param StorageInterface         $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string                   $baseUrl
     * @param string                   $capturePath
     * @param string                   $notifyPath
     * @param string                   $authorizePath
     * @param string                   $refundPath
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $baseUrl, $capturePath, $notifyPath, $authorizePath, $refundPath)
    {
        parent::__construct($tokenStorage, $storageRegistry, $capturePath, $notifyPath, $authorizePath, $refundPath);

        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateUrl($path, array $parameters = array())
    {
        $url = Url::createFromUrl($this->baseUrl);
        $url->getPath()->set($path);
        $url->getQuery()->set($parameters);

        return (string) $url;
    }
}
