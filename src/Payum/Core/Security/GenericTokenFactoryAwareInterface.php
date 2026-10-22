<?php

namespace Payum\Core\Security;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A handler is given the token factory as
 *             Payum\Core\Handler\Context::tokens(), so nothing has to be injected into it.
 */
interface GenericTokenFactoryAwareInterface
{
    public function setGenericTokenFactory(?GenericTokenFactoryInterface $genericTokenFactory = null);
}
