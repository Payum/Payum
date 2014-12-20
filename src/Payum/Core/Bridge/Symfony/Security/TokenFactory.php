<?php
namespace Payum\Core\Bridge\Symfony\Security;

use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractGenericTokenFactory;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenFactory extends AbstractGenericTokenFactory
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $urlGenerator;

    /**
     * @param UrlGeneratorInterface    $urlGenerator
     * @param StorageInterface         $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string                   $capturePath
     * @param string                   $notifyPath
     * @param string                   $autorizePath
     * @param string                   $refundPath
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $capturePath, $notifyPath, $autorizePath, $refundPath)
    {
        $this->urlGenerator = $urlGenerator;

        parent::__construct($tokenStorage, $storageRegistry, $capturePath, $notifyPath, $autorizePath, $refundPath);
    }

    /**
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    protected function generateUrl($path, array $parameters = array())
    {
        return $this->urlGenerator->generate($path, $parameters, true);
    }
}
