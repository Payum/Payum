<?php
namespace Payum\Core\Bridge\Symfony\Security;

use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenFactory extends AbstractTokenFactory
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $urlGenerator;

    /**
     * @param StorageInterface         $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param UrlGeneratorInterface    $urlGenerator
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

        parent::__construct($tokenStorage, $storageRegistry);
    }

    /**
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    protected function generateUrl($path, array $parameters = array())
    {
        return $this->urlGenerator->generate($path, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
