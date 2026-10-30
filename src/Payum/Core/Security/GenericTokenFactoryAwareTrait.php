<?php

namespace Payum\Core\Security;

/**
 * @deprecated since 2.0, removed in 3.0. Port the action to a handler and mint tokens with
 *             {@see \Payum\Core\Handler\Context::tokens()}, which returns a {@see \Payum\Core\Security\TokenIssuerInterface}.
 */
trait GenericTokenFactoryAwareTrait
{
    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    public function setGenericTokenFactory(?GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }
}
