<?php
namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;

class HttpRequestVerifierBuilder
{
    /**
     * @param StorageInterface $tokenStorage
     *
     * @return HttpRequestVerifierInterface
     */
    public function build(StorageInterface $tokenStorage)
    {
        return new HttpRequestVerifier($tokenStorage);
    }
}