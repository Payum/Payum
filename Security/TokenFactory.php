<?php
namespace Payum\Bundle\PayumBundle\Security;

use Symfony\Component\Routing\RouterInterface;
use Payum\Core\Bridge\Symfony\Security\TokenFactory as BaseTokenFactory;

/**
 * @deprecated since 0.8.1 will be removed in 0.9. Use TokenFactory from bridge.
 */
class TokenFactory extends BaseTokenFactory
{
}