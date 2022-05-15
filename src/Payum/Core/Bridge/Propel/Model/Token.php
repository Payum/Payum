<?php

namespace Payum\Core\Bridge\Propel\Model;

use om\BaseToken;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\Util\Random;
use function trigger_error;

@trigger_error('Propel storage is deprecated and will be removed in V2', \E_USER_DEPRECATED);
class Token extends BaseToken implements TokenInterface
{
    public function __construct()
    {
        $this->setHash(Random::generateToken());
    }
}
