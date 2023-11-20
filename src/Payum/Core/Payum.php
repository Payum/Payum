<?php

namespace Payum\Core;

use Exception;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request as PayumRequest;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
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
        RegistryInterface            $registry,
        HttpRequestVerifierInterface $httpRequestVerifier,
        GenericTokenFactoryInterface $tokenFactory,
        StorageInterface             $tokenStorage
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
     * @param Request|array<string, mixed>|null $request
     *
     * @throws Exception
     */
    public function capture(Request | array | null $request = null): Response
    {
        $token = $this->httpRequestVerifier->verify($request ?: Request::createFromGlobals());

        $gateway = $this->getGateway($token->getGatewayName());

        $reply = $gateway->execute(new PayumRequest\Capture($token), true);

        $this->httpRequestVerifier->invalidate($token);

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
}
