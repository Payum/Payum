<?php
namespace Payum\Bundle\PayumBundle\Security;

use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractGenericTokenFactory;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @deprecated since 0.8.1 will be removed in 0.9. Use TokenFactory from bridge.
 */
class TokenFactory extends AbstractGenericTokenFactory
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $urlGenerator
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string $capturePath
     * @param string $notifyPath
     */
    public function __construct(RouterInterface $urlGenerator, StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $capturePath, $notifyPath)
    {
        $this->router = $urlGenerator;

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