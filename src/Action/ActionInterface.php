<?php
namespace Paymnt\Action;

use Paymnt\Operation\OperationInterface;

interface ActionInterface
{
    function manage(OperationInterface $operation);

    function supports(OperationInterface $operation);
}