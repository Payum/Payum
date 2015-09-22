<?php
namespace Payum\Core\Bridge\Guzzle;

use Payum\Core\HttpControllerInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;
use Payum\Core\Exception\LogicException;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;

class HttpController implements HttpControllerInterface
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var HttpRequestVerifierInterface
     */
    protected $httpRequestVerifier;

    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @param RegistryInterface            $registry
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     * @param GenericTokenFactoryInterface $tokenFactory
     */
    public function __construct(
        RegistryInterface $registry,
        HttpRequestVerifierInterface $httpRequestVerifier,
        GenericTokenFactoryInterface $tokenFactory
    ) {
        $this->registry = $registry;
        $this->httpRequestVerifier = $httpRequestVerifier;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function authorize(ServerRequestInterface $request)
    {
        return $this->generic($request, Authorize::class);
    }

    /**
     * {@inheritDoc}
     */
    public function capture(ServerRequestInterface $request)
    {
        return $this->generic($request, Capture::class);
    }

    /**
     * {@inheritDoc}
     */
    public function refund(ServerRequestInterface $request)
    {
        return $this->generic($request, Refund::class);
    }

    /**
     * {@inheritDoc}
     */
    public function sync(ServerRequestInterface $request)
    {
        return $this->generic($request, Sync::class);
    }

    /**
     * {@inheritDoc}
     */
    public function notify(ServerRequestInterface $request)
    {
        return $this->generic($request, Notify::class);
    }

    /**
     * {@inheritDoc}
     */
    public function notifyUnsafe(ServerRequestInterface $request)
    {
        try {
            $gateway = $this->registry->getGateway($request->getAttribute('gateway'));
            $gateway->execute(new Notify(null));
        } catch (ReplyInterface $reply) {
            return $this->replyToResponse($reply);
        }

        return new Response(204);
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $requestClass
     *
     * @return Response
     */
    protected function generic(ServerRequestInterface $request, $requestClass)
    {
        try {
            $token = $this->httpRequestVerifier->verify($_SERVER);

            $gateway = $this->registry->getGateway($token->getGatewayName());
            $gateway->execute(new $requestClass($token));

            $this->httpRequestVerifier->invalidate($token);

            if ($token->getAfterUrl()) {
                return $this->redirect($token->getAfterUrl());
            }

            return new Response('', 204);
        } catch (ReplyInterface $reply) {
            return $this->replyToResponse($reply);
        }
    }


    /**
     * @param string $redirectUrl
     *
     * @return Response
     */
    protected function redirect($redirectUrl)
    {
        $body = sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8'));

        return new Response(302, ['location' => $redirectUrl, $body]);
    }

    protected function replyToResponse(ReplyInterface $reply)
    {
        if ($reply instanceof HttpResponse) {
            return new Response(
                $reply->getStatusCode(),
                $reply->getHeaders(),
                $reply->getContent()
            );
        }

        $ro = new \ReflectionObject($reply);

        throw new LogicException(
            sprintf('Cannot convert reply %s to psr7 response.', $ro->getShortName()),
            null,
            $reply
        );
    }
}