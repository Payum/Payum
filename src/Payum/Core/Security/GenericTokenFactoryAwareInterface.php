<?php
namespace Payum\Core\Security;

interface GenericTokenFactoryAwareInterface
{
    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null): void;
}
