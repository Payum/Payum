<?php
namespace Payum\Core\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Security\TokenProviderInterface;
use Payum\Core\Security\TokenProviderAwareInterface;

class TokenProviderExtension extends BaseExtension implements TokenProviderAwareInterface
{
    /**
     * @var TokenProviderInterface
     */
    protected $provider;

    /**
     * {@inheritDoc}
     */
    public function setTokenProvider(TokenProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        if($this->provider && $action instanceof TokenProviderAwareInterface) {
            $action->setTokenProvider($this->provider);
        }
    }

}