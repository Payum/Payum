<?php
namespace Payum\Core\Security;

interface GenericTokenFactoryAwareInterface
{
    /**
     * @param GenericTokenFactoryInterface $genericTokenFactory
     *
     * @return void
     */
    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null);
}
