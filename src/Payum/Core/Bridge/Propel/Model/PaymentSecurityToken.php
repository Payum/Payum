<?php

namespace Payum\Core\Bridge\Propel\Model;

use Payum\Core\Bridge\Propel\Model\om\BasePaymentSecurityToken;

use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\Util\Random;

class PaymentSecurityToken extends BasePaymentSecurityToken implements TokenInterface
{
    public function __construct()
    {
        $this->setHash(Random::generateToken());
    }
}