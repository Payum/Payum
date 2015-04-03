<?php
namespace Payum\Core;

class Gateway extends Payment implements GatewayInterface
{
    /**
     * {@inheritDoc}
     */
    protected function findActionSupported($request)
    {
        $action = parent::findActionSupported($request);

        if ($action && $action instanceof GatewayAwareInterface) {
            $action->setGateway($this);
        }

        return $action;
    }
}
