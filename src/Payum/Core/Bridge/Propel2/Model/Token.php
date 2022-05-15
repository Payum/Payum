<?php
namespace Payum\Core\Bridge\Propel2\Model;

use Payum\Core\Bridge\Propel2\Model\Base\Token as BaseToken;
use function trigger_error;

@trigger_error('Propel storage is deprecated and will be removed in V2', \E_USER_DEPRECATED);
class Token extends BaseToken
{
}
