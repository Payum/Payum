<?php

namespace Payum\Core;

use Exception;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\Result;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @template StorageType of object
 * @implements RegistryInterface<StorageType>
 */
class Payum implements RegistryInterface
{
    /**
     * @var RegistryInterface<StorageType>
     */
    protected RegistryInterface $registry;

    protected HttpRequestVerifierInterface $httpRequestVerifier;

    protected GenericTokenFactoryInterface $tokenFactory;

    /**
     * @var StorageInterface<TokenInterface>
     */
    protected StorageInterface $tokenStorage;

    /**
     * @param RegistryInterface<StorageType> $registry
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    public function __construct(
        RegistryInterface $registry,
        HttpRequestVerifierInterface $httpRequestVerifier,
        GenericTokenFactoryInterface $tokenFactory,
        StorageInterface $tokenStorage
    ) {
        $this->registry = $registry;
        $this->httpRequestVerifier = $httpRequestVerifier;
        $this->tokenFactory = $tokenFactory;
        $this->tokenStorage = $tokenStorage;
    }

    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        return $this->registry->getGatewayFactory($name);
    }

    public function getGatewayFactories(): array
    {
        return $this->registry->getGatewayFactories();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getGateway(string $name): GatewayInterface
    {
        return $this->registry->getGateway($name);
    }

    public function getGateways(): array
    {
        return $this->registry->getGateways();
    }

    /**
     * @param class-string<StorageType> $class
     * @return StorageInterface<StorageType>
     */
    public function getStorage($class): StorageInterface
    {
        return $this->registry->getStorage($class);
    }

    /**
     * @return array<class-string, StorageInterface<StorageType>>
     */
    public function getStorages(): array
    {
        return $this->registry->getStorages();
    }

    public function getHttpRequestVerifier(): HttpRequestVerifierInterface
    {
        return $this->httpRequestVerifier;
    }

    public function getTokenFactory(): GenericTokenFactoryInterface
    {
        return $this->tokenFactory;
    }

    /**
     * @return StorageInterface<TokenInterface>
     */
    public function getTokenStorage(): StorageInterface
    {
        return $this->tokenStorage;
    }

    /**
     * Persist the model and create the capture token that starts the payment.
     *
     * The storage is resolved from the model's own class, so any model with a registered
     * storage works here: a Payment, an ArrayObject of gateway details, or your own order
     * class. When no after path is given the one configured on the builder is used.
     *
     * @param array<string, mixed> $afterParameters
     *
     * @throws InvalidArgumentException if the model has no registered storage
     */
    public function prepare(
        string $gatewayName,
        object $model,
        ?string $afterPath = null,
        array $afterParameters = []
    ): TokenInterface {
        $this->getStorage($model::class)->update($model);

        return $this->tokenFactory->createCaptureToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    /**
     * @param Request|array<string, mixed>|null $request
     *
     * @throws Exception
     */
    public function capture(Request | array | null $request = null): Response
    {
        $token = $this->httpRequestVerifier->verify($request ?: Request::createFromGlobals());

        $gateway = $this->getGateway($token->getGatewayName());

        if ($gateway instanceof Gateway && $gateway->supportsCommand(CaptureCommand::class)) {
            return $this->respondTo($gateway->execute(new CaptureCommand($token)), $token);
        }

        $reply = $gateway->execute(new Capture($token), true);

        if ($reply instanceof HttpRedirect) {
            return new RedirectResponse($reply->getUrl(), $reply->getStatusCode(), $reply->getHeaders());
        }

        if ($reply instanceof HttpPostRedirect) {
            return new Response($reply->getContent(), $reply->getStatusCode(), $reply->getHeaders());
        }

        return new RedirectResponse($token->getAfterUrl());
    }

    /**
     * @param Request|array<string, mixed>|null $request
     *
     * @throws Exception
     */
    public function done(Request | array | null $request = null): PaymentInterface
    {
        $token = $this->getHttpRequestVerifier()->verify($request ?: Request::createFromGlobals());
        $gateway = $this->getGateway($token->getGatewayName());

        $this->httpRequestVerifier->invalidate($token);

        $gateway->execute($status = new GetHumanStatus($token));

        return $status->getFirstModel();
    }

    /**
     * @param Request|array<string, mixed>|null $request
     *
     * @throws Exception
     */
    public function notify(Request | array | null $request = null): Response
    {
        $token = $this->httpRequestVerifier->verify($request ?: Request::createFromGlobals());
        $gateway = $this->getGateway($token->getGatewayName());

        try {
            $gateway->execute(new Notify($token));

            return new Response('', Response::HTTP_NO_CONTENT);
        } catch (HttpResponse $reply) {
            return new Response($reply->getContent(), $reply->getStatusCode(), $reply->getHeaders());
        } catch (ReplyInterface $reply) {
            throw new LogicException('Unsupported reply', $reply->getCode(), $reply);
        }
    }

    /**
     * Turns what a handler decided into an HTTP response.
     *
     * A null next action means there is nothing left for the customer to do, so the application takes
     * over at the token's after URL.
     */
    private function respondTo(Result $result, TokenInterface $token): Response
    {
        $next = $result->next;

        if ($next instanceof Redirect) {
            return new RedirectResponse($next->url, $next->statusCode, $next->headers);
        }

        if ($next instanceof PostRedirect) {
            $reply = new HttpPostRedirect($next->url, $next->fields);

            return new Response($reply->getContent(), $reply->getStatusCode(), $reply->getHeaders());
        }

        return new RedirectResponse($token->getAfterUrl());
    }
}
