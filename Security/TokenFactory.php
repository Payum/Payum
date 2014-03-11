<?php
namespace Payum\Bundle\PayumBundle\Security;

use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractGenericTokenFactory;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\RouterInterface;

class TokenFactory extends AbstractGenericTokenFactory
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string $capturePath
     * @param string $notifyPath
     */
    public function __construct(RouterInterface $router, StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $capturePath, $notifyPath)
    {
        $this->router = $router;

        parent::__construct($tokenStorage, $storageRegistry, $capturePath, $notifyPath);
    }

    /**
     * @param string $path
     * @param array $parameters
     *
     * @return string
     */
    protected function generateUrl($path, array $parameters = array())
    {
        return $this->router->generate($path, $parameters, $absolute = true);
    }
}