<?php

namespace Payum\Core\Bridge\Propel\Model;

use om\BasePayment;
use Payum\Core\Model\PaymentInterface;
use function trigger_error;

@trigger_error('Propel storage is deprecated and will be removed in V2', \E_USER_DEPRECATED);
class Payment extends BasePayment implements PaymentInterface
{
}
