<?php
namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\PaymentConfig as BasePaymentConfig;

/**
 * @ORM\Entity
 */
class PaymentConfig extends BasePaymentConfig
{
}
