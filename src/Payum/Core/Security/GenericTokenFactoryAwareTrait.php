<?php

namespace Payum\Core\Security;

trait GenericTokenFactoryAwareTrait
{
    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @param GenericTokenFactoryInterface $genericTokenFactory
     */
    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null)
    {
        $this->tokenFactory = $genericTokenFactory;
    }
}
