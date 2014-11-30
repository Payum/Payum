<?php
namespace Payum\Core\Bridge\Symfony\Security;

use Payum\Core\Exception\TokenFactoryException;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\TokenFactory as BaseTokenFactory;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenFactory extends BaseTokenFactory
{

    /**
     *
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string $tokenParameter
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $tokenParameter = 'payum_token')
    {
        parent::__construct($tokenStorage, $storageRegistry, $tokenParameter);
        $this->urlGenerator = $urlGenerator;
    }

    /**
     *
     * @param string $path
     * @param array $parameters
     *
     * @return string
     */
    protected function generateUrl($path, array $parameters = null)
    {
        try {
            return $this->urlGenerator->generate($path, $parameters, true);
        } catch (RouteNotFoundException $e) {
            return parent::generateUrl($path, $parameters);
        } catch (\Exception $e) {
            throw TokenFactoryException::couldNotGenerateUrlFor($path, $parameters, $e);
        }
    }
}
