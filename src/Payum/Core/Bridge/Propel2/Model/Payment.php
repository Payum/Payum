<?php
namespace Payum\Core\Bridge\Propel2\Model;

use Payum\Core\Bridge\Propel2\Model\Base\Payment as BasePayment;
use function trigger_error;

@trigger_error('Propel storage is deprecated and will be removed in V2', \E_USER_DEPRECATED);
class Payment extends BasePayment
{
}
