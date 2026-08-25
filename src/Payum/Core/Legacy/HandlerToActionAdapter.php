<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Http\Discovery\Psr17FactoryDiscovery;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\PrependActionInterface;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Gateway as Executor;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Middleware\PersistStateMiddleware;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Middleware\RecordPaymentStatusMiddleware;
use Payum\Core\Middleware\TemplateRenderMiddleware;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Result\NextAction\RenderTemplate as RenderTemplateAction;
use Payum\Core\Result\Result;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use function sprintf;

/**
 * Presents a 2.0 handler as the 1.x action for the request that means the same thing.
 *
 * The other half of {@see ActionToHandlerAdapter}: a gateway a 1.x factory still assembles registers one of
 * these where it used to register an action, and the handler behind it is reached by everything that
 * already talks to that gateway. Nothing else about the gateway has to change, so the first handler can
 * land on its own.
 *
 * ```php
 * protected function populateConfig(ArrayObject $config): void
 * {
 *     $config->defaults([
 *         'payum.action.capture' => fn (ArrayObject $config) => new HandlerToActionAdapter(
 *             new CaptureHandler($config['payum.api']),
 *         ),
 *     ]);
 * }
 * ```
 *
 * Which request it answers is read off the handler, so there is nothing to keep in step. It is prepended,
 * because a handler wants the payment the request is about and core's own actions would otherwise have
 * unwrapped it down to a details array first.
 *
 * What the handler gets is a real {@see Context} -- state, token, HTTP request, token factory -- and the
 * middleware that persists what it decides. Two things it does not get:
 *
 *   - {@see Context::gateway()}, which is null: there is no gateway class describing a gateway a factory
 *     assembled.
 *   - {@see Context::execute()}, which needs the gateway to have handlers registered on it. A handler
 *     dispatching sub-commands is past what this adapter is for; port the gateway.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class HandlerToActionAdapter implements ActionInterface, PrependActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    /**
     * @var class-string<CommandInterface<Result>>
     */
    private readonly string $commandClass;

    private ?Executor $executor = null;

    private ?GenericTokenFactoryInterface $tokenFactory = null;

    public function __construct(
        private readonly HandlerInterface $handler
    ) {
        $commands = HandlerMap::fromHandlers([$handler::class])->commands();

        $this->commandClass = $commands[0];
    }

    public function execute($request): void
    {
        $executor = $this->executor;

        if (! $executor instanceof Executor) {
            throw new LogicException(sprintf(
                '%s has not been given the gateway it runs on, so it cannot build the context %s needs. Add it to a %s.',
                self::class,
                $this->handler::class,
                Executor::class,
            ));
        }

        $command = RequestToCommand::translate($request);

        if (! $command instanceof CommandInterface || ! $command instanceof $this->commandClass) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $result = $this->pipeline()->process(
            $command,
            $this->context($executor, $command, $request),
            $this->handle(...),
        );

        $this->reply($executor, $result);
    }

    public function setGateway(GatewayInterface $gateway): void
    {
        // Building a context needs the executor itself, not merely something that can execute.
        $this->executor = $gateway instanceof Executor ? $gateway : null;
    }

    public function setGenericTokenFactory(?GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    public function supports($request): bool
    {
        return RequestToCommand::supports($request)
            && RequestToCommand::translate($request) instanceof $this->commandClass;
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function context(Executor $executor, CommandInterface $command, object $request): Context
    {
        $model = $request instanceof Generic ? $request->getFirstModel() : null;

        return new Context(
            $executor,
            $command,
            null,
            $this->httpRequest($executor),
            $this->tokenFactory,
            $model instanceof SubjectInterface ? $model : $command->subject(),
            $command->token(),
            [],
            $executor->getName(),
        );
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function handle(CommandInterface $command, Context $context): Result
    {
        if ($this->handler instanceof NotifyHandlerInterface && $command instanceof NotifyCommand) {
            return $this->handler->handle($command, $this->handler->verify($context->httpRequest()), $context);
        }

        if (! method_exists($this->handler, 'handle')) {
            throw new LogicException(sprintf('%s must declare handle().', $this->handler::class));
        }

        $result = $this->handler->handle($command, $context);

        if (! $result instanceof Result) {
            throw new LogicException(sprintf('%s::handle() must return a %s.', $this->handler::class, Result::class));
        }

        return $result;
    }

    /**
     * The inbound request as PSR-7, rebuilt from what 1.x knows about it.
     *
     * There is no PSR-7 request to be had inside a gateway a factory assembled, so this asks for the one
     * shape that is always available and converts it. The body matters: a notify handler verifies a
     * signature over the raw bytes.
     */
    private function httpRequest(Executor $executor): ServerRequestInterface
    {
        $executor->execute($http = new GetHttpRequest());

        $request = Psr17FactoryDiscovery::findServerRequestFactory()
            ->createServerRequest($http->method ?: 'GET', $http->uri ?: '/')
            ->withQueryParams($http->query)
            ->withParsedBody($http->request)
            ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream($http->content))
        ;

        foreach ($http->headers as $name => $value) {
            $request = $request->withHeader((string) $name, $value);
        }

        return $request;
    }

    /**
     * The middleware a handler is entitled to whichever way it was reached. The gateway's own extensions
     * already run around this, on the action path, and the storage extension is what writes the payment
     * away -- which is why nothing is passed to PersistStateMiddleware.
     */
    private function pipeline(): Pipeline
    {
        return new Pipeline([
            new TemplateRenderMiddleware(),
            new PersistStateMiddleware(),
            new RecordPaymentStatusMiddleware(),
        ]);
    }

    /**
     * Answers the 1.x caller the way it expects to hear it: by throwing.
     */
    private function reply(Executor $executor, Result $result): void
    {
        if ($result->next instanceof RenderTemplateAction) {
            $executor->execute($render = new RenderTemplate($result->next->template, $result->next->context));

            throw new HttpResponse($render->getResult());
        }

        $reply = ResultToReply::translate($result);

        if ($reply instanceof Base) {
            throw $reply;
        }
    }
}
