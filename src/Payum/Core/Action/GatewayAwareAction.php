<?php
namespace Payum\Core\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;

/**
 * @deprecated  since 1.3 will be removed in 2.0.  Use trait+interface in your classes.
 */
abstract class GatewayAwareAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
}
