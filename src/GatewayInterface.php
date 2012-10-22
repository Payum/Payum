<?php
namespace Paymnt;

use Paymnt\Action\ActionInterface;

interface GatewayInterface extends ActionInterface 
{    
    function getName();
}