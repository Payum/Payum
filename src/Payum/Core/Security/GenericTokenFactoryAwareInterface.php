<?php

namespace Payum\Core\Security;

interface GenericTokenFactoryAwareInterface
{
    /**
     * @param GenericTokenFactoryInterface $genericTokenFactory
     */
    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null);
}
