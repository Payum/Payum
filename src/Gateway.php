<?php
namespace Paymnt;

use Paymnt\GatewayInterface;
use Paymnt\Action\ActionInterface;
use Paymnt\Operation\OperationInterface;

abstract class Gateway implements GatewayInterface
{
    protected $actions = array();
    
    public function addAction(ActionInterface $action)
    {
        $this->actions[] = $action;
    }
    
    public function manage(OperationInterface $operation)
    {
        $action = $this->findManagerSupportedOperation($operation);
        
        return $action->manage($operation);
    }

    public function supports(OperationInterface $operation)
    {
        return (boolean) $this->findManagerSupportedOperation($operation);
    }

    protected function findManagerSupportedOperation(OperationInterface $operation)
    {
        foreach ($this->actions as $action) {
            if ($action->supports($operation)) {
                return $action;
            }
        }
    }
}