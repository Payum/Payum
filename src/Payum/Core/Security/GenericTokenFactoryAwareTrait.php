<?php
namespace Payum\Core\Security;

trait GenericTokenFactoryAwareTrait
{
    protected GenericTokenFactoryInterface $tokenFactory;

    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }
}
