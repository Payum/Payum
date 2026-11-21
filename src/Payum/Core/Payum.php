<?php

namespace Payum\Core;

use Exception;
use Http\Discovery\Psr17FactoryDiscovery;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Legacy\GenericTokenFactoryIssuer;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\Result;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Security\TokenPurpose;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Template\RendererInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function trigger_deprecation;

/**
 * @template TModel of object
 * @implements RegistryInterface<TModel>
 */
class Payum implements RegistryInterface
{
    /**
     * @var RegistryInterface<TModel>
     */
    protected RegistryInterface $registry;

    protected HttpRequestVerifierInterface $httpRequestVerifier;

    protected GenericTokenFactoryInterface $tokenFactory;

    /**
     * @var StorageInterface<TokenInterface>
     */
    protected StorageInterface $tokenStorage;

    protected ?RendererInterface $renderer;

    protected TokenIssuerInterface $tokenIssuer;

    private ServerRequestFactoryInterface $serverRequestFactory;

    private StreamFactoryInterface $streamFactory;

    /**
     * @param RegistryInterface<TModel> $registry
     * @param StorageInterface<TokenInterface> $tokenStorage
     * @param RendererInterface|null $renderer optional only so hand-built instances keep working; PayumBuilder always supplies one
     */
    public function __construct(
        RegistryInterface $registry,
        HttpRequestVerifierInterface $httpRequestVerifier,
        GenericTokenFactoryInterface $tokenFactory,
        StorageInterface $tokenStorage,
        ?RendererInterface $renderer = null,
        ?TokenIssuerInterface $tokenIssuer = null,
        ?ServerRequestFactoryInterface $serverRequestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->registry = $registry;
        $this->httpRequestVerifier = $httpRequestVerifier;
        $this->tokenFactory = $tokenFactory;
        $this->tokenStorage = $tokenStorage;
        $this->renderer = $renderer;
        $this->tokenIssuer = $tokenIssuer ?? GenericTokenFactoryIssuer::wrap($tokenFactory);
        $this->serverRequestFactory = $serverRequestFactory ?? Psr17FactoryDiscovery::findServerRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * Renders the template a RenderTemplate result names.
     *
     * One renderer serves every gateway, which is why it lives here rather than on a gateway: a gateway
     * cannot replace it, and reaching it through one implied otherwise.
     */
    public function renderer(): RendererInterface
    {
        if (! $this->renderer instanceof RendererInterface) {
            throw new LogicException(sprintf(
                'This %s was built without a renderer. Build it with %s, or pass a %s to the constructor.',
                self::class,
                PayumBuilder::class,
                RendererInterface::class,
            ));
        }

        return $this->renderer;
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
     * @param class-string<TModel> $class
     * @return StorageInterface<TModel>
     */
    public function getStorage($class): StorageInterface
    {
        return $this->registry->getStorage($class);
    }

    /**
     * @return array<class-string, StorageInterface<TModel>>
     */
    public function getStorages(): array
    {
        return $this->registry->getStorages();
    }

    public function getHttpRequestVerifier(): HttpRequestVerifierInterface
    {
        return $this->httpRequestVerifier;
    }

    /**
     * @deprecated since 2.0, removed in 3.0. Use {@see getTokenIssuer()} instead.
     */
    public function getTokenFactory(): GenericTokenFactoryInterface
    {
        trigger_deprecation(
            'payum/core',
            '2.0.0',
            '%s() is deprecated and will be removed in 3.0. Use %s::getTokenIssuer(), which returns a %s, instead.',
            __METHOD__,
            self::class,
            TokenIssuerInterface::class,
        );

        return $this->tokenFactory;
    }

    public function getTokenIssuer(): TokenIssuerInterface
    {
        return $this->tokenIssuer;
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

        return $this->tokenIssuer->create(TokenPurpose::Capture, $gatewayName, $model, $afterPath, $afterParameters);
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

        return $this->redirectToAfterUrl($token);
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

        if ($gateway instanceof Gateway && $gateway->supportsCommand(CaptureCommand::class)) {
            $identity = $token->getDetails();

            return $this->getStorage($identity->getClass())->find($identity);
        }

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

        if ($gateway instanceof Gateway && $gateway->supportsCommand(NotifyCommand::class)) {
            return $this->acknowledge($gateway, NotifyCommand::forToken($token));
        }

        return $this->notifyLegacy($gateway, new Notify($token));
    }

    /**
     * Receives a webhook on a route the application owns, such as the one URL set in a PSP's dashboard.
     *
     * The gateway finds what the message is about from the references it carries. Without a request the
     * gateway reads its own ServerRequestInterface service; with one, that request is verified instead.
     *
     * @throws Exception
     */
    public function webhook(string $gatewayName, ?Request $request = null): Response
    {
        $gateway = $this->getGateway($gatewayName);

        if (! $gateway instanceof Gateway || ! $gateway->supportsCommand(NotifyCommand::class)) {
            return $this->notifyLegacy($gateway, new Notify(null));
        }

        if (null === $request) {
            return $this->acknowledge($gateway, NotifyCommand::forGateway());
        }

        try {
            $event = $gateway->verifyWebhook($this->toServerRequest($request));
        } catch (WebhookNotVerifiedException) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        return $this->acknowledge($gateway, NotifyCommand::forGateway()->withEvent($event));
    }

    private function notifyLegacy(GatewayInterface $gateway, Notify $notify): Response
    {
        try {
            $gateway->execute($notify);

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

        if (! $next instanceof NextAction) {
            return $this->redirectToAfterUrl($token);
        }

        if ($next instanceof Redirect) {
            return new RedirectResponse($next->url, $next->statusCode, $next->headers);
        }

        if ($next instanceof PostRedirect) {
            $reply = new HttpPostRedirect($next->url, $next->fields);

            return new Response($reply->getContent(), $reply->getStatusCode(), $reply->getHeaders());
        }

        if ($next instanceof RenderTemplate) {
            return new Response($this->renderer()->render($next->template, $next->context));
        }

        throw new LogicException(sprintf(
            '%s cannot turn %s into a response; it handles %s, %s and %s. Execute the command yourself and act on the result.',
            __METHOD__,
            $next::class,
            Redirect::class,
            PostRedirect::class,
            RenderTemplate::class,
        ));
    }

    /**
     * Runs a webhook and turns the handler's answer into the response the PSP is waiting for.
     *
     * A handler that named no acknowledgement gets 204, which every PSP accepts. A message that failed
     * verification gets 400 with nothing in it; the exception carries the reason, for the application to
     * log.
     */
    private function acknowledge(Gateway $gateway, NotifyCommand $command): Response
    {
        try {
            $result = $gateway->execute($command);
        } catch (WebhookNotVerifiedException) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $acknowledgement = $result instanceof NotifyResult && $result->acknowledgement instanceof Acknowledgement
            ? $result->acknowledgement
            : Acknowledgement::noContent();

        return new Response($acknowledgement->body, $acknowledgement->status, $acknowledgement->headers);
    }

    private function toServerRequest(Request $request): ServerRequestInterface
    {
        $serverRequest = $this->serverRequestFactory->createServerRequest($request->getMethod(), $request->getUri(), $request->server->all())
            ->withBody($this->streamFactory->createStream($request->getContent()))
            ->withQueryParams($request->query->all())
            ->withParsedBody($request->request->all())
            ->withCookieParams($request->cookies->all());

        foreach ($request->headers->all() as $name => $values) {
            $serverRequest = $serverRequest->withHeader($name, $values);
        }

        return $serverRequest;
    }

    private function redirectToAfterUrl(TokenInterface $token): RedirectResponse
    {
        $afterUrl = $token->getAfterUrl();

        if (! $afterUrl) {
            throw new LogicException(sprintf(
                'The token "%s" has no after URL to send the customer to. When a token\'s after path is itself a Payum operation, such as a capture that follows an authorize, pass a final path when creating it.',
                $token->getHash(),
            ));
        }

        return new RedirectResponse($afterUrl);
    }
}
