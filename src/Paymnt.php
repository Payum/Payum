<?php
namespace Paymnt;

use Paymnt\GatewayInterface;
use Paymnt\Operation\InteractiveOperationInterface;
use Paymnt\Operation\ConvertOperation;
use Paymnt\Exception\RuntimeException;

class Paymnt
{
    protected $gateways = array();
    
    protected function setGateway(GatewayInterface $gateway)
    {
        $this->gateways[$gateway->getName()] = $gateway;
    }
    
    public function manage($operation, $gateway)
    {
        /** @var $gateway GatewayInterface */
        if (false == $gateway instanceof GatewayInterface) {
            if (false == (is_string($gateway) && isset($this->gateways[$gateway]))) {
                throw new RuntimeException('The gateway was not found.');
            }

            $gateway = $this->gateways[$gateway];
        }
        
        if (false == $gateway->supports($operation)) {
            $operation = new ConvertOperation($operation);
            $gateway->manage($operation);
            if (false == $operation->getTargetOperation()) {
                throw new RuntimeException('The operation is not supported.');
            }
            
            $operation = $operation->getTargetOperation();
        }
        
        $requireOperation = $operation;
        do {
            $requireOperation = $gateway->manage($requireOperation);
            if ($requireOperation instanceof InteractiveOperationInterface) {
                return $requireOperation;
            }
        } while ($requireOperation == false);
    }
}