<?php

namespace Payum\Core\Bridge\PlainPhp\Security;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Security\HttpRequestVerifier as ServerRequestVerifier;
use Payum\Core\Security\TokenInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use function array_merge;

/**
 * Also takes an HttpFoundation request, or the $_REQUEST array (deprecated).
 */
class HttpRequestVerifier extends ServerRequestVerifier
{
    public function verify($httpRequest): TokenInterface
    {
        if ($httpRequest instanceof ServerRequestInterface) {
            return parent::verify($httpRequest);
        }

        if ($httpRequest instanceof Request) {
            return $this->verifyToken(
                array_merge($httpRequest->request->all(), $httpRequest->query->all())[$this->tokenParameter] ?? null,
                // getUri() appends the path info, so a script requested directly reads as /capture.php/?...
                $httpRequest->getSchemeAndHttpHost() . $httpRequest->getRequestUri()
            );
        }

        if (! is_array($httpRequest)) {
            throw new InvalidArgumentException('Invalid request given. In most cases you have to pass $_REQUEST array.');
        }

        trigger_deprecation('payum/core', '2.0.0', 'Passing an array to %s::verify() is deprecated and removed in 3.0, because it reads the URL from $_SERVER. Pass a %s instead.', self::class, ServerRequestInterface::class);

        return $this->verifyToken($httpRequest[$this->tokenParameter] ?? null, $_SERVER['REQUEST_URI'] ?? '');
    }
}
