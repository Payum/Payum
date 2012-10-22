<?php
namespace Paymnt\Operation;

class AuthorizeOperation implements OperationInterface
{
    protected $operationToBeAuth;
    
    public function __construct(OperationInterface $operationToBeAuth)
    {
        $this->operationToBeAuth = $operationToBeAuth;
    }
    
    public function getOperationToBeAuth()
    {
        return $this->operationToBeAuth;
    }
}