<?php

namespace Payum\Core\Bridge\Propel\Model;

use om\BaseToken;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\Util\Random;

class Token extends BaseToken implements TokenInterface
{
    public function __construct()
    {
        $this->setHash(Random::generateToken());
    }
}
