<?php
namespace Payum\Core\Security;

interface TokenProviderAwareInterface
{

    /**
     *
     * @param TokenProviderInterface $provider
     * @return void
     */
    public function setTokenProvider(TokenProviderInterface $provider);
}
