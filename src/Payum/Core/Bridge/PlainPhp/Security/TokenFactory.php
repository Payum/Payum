<?php
namespace Payum\Core\Bridge\PlainPhp\Security;

use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Components\Query;
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
            $uri = HttpUri::createFromServer($_SERVER);
            $scheme = $uri->getScheme();
            $auth = $uri->getAuthority();
            if ('' != $auth && '' == $scheme) {
                $scheme = '//';
            }
            $this->baseUrl = $scheme . $auth;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function generateUrl($path, array $parameters = array())
    {

        $uri = HttpUri::createFromString($this->baseUrl);
        $uri = $uri->withPath($path);
        $uri = $uri->withQuery((string)Query::createFromArray($parameters));

        return (string)$uri;
    }
}
