<?php
namespace Payum\Bundle\PayumBundle\Security;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier as BaseHttpRequestVerifier;

/**
 * @deprecated since 0.8.1 will be removed in 0.9. Use HttpRequestVerifier from bridge
 */
class HttpRequestVerifier extends BaseHttpRequestVerifier
{
}