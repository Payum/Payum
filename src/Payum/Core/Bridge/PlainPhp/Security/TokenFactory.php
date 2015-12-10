<?php
namespace Payum\Core\Bridge\PlainPhp\Security;

use League\Url\Url;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Storage\StorageInterface;

class TokenFactory extends AbstractTokenFactory
{
    /**
     * @var string
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

        if ($baseUrl) {
            $this->baseUrl = $baseUrl;
        } else {
            $this->baseUrl = Url::createFromServer($_SERVER)->getBaseUrl();
        }
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
