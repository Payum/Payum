<?php

namespace Payum\Core;

use Exception;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Exception\CommandNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context as HandlerContext;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Legacy\RequestToCommand;
use Payum\Core\Legacy\ResultToReply;
use Payum\Core\Legacy\StatusMarker;
use Payum\Core\Middleware\MiddlewareCollection;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Model\PaymentStatuses;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Result\Result;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Template\RendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionProperty;
use Throwable;
use function func_num_args;
use function trigger_deprecation;

class Gateway implements GatewayInterface
{
    /**
     * @var list<class-string<Action\ActionInterface>|Action\ActionInterface>
     */
    protected array $actions = [];

    /**
     * @var mixed[]
     * @deprecated since 2.0. BC will be removed in 3.x. Use dependency-injection to inject the api into the action instead.
     */
    protected $apis = [];

    /**
     * @var ExtensionCollection
     */
    protected $extensions;

    /**
     * @var Context[]
     */
    protected array $stack = [];

    protected ContainerInterface $container;

    protected ?HandlerMap $handlerMap = null;

    protected ?Pipeline $pipeline = null;

    /**
     * The name this gateway is registered under. Set by the builder; null when built by hand.
     */
    protected ?string $name = null;

    /**
     * @var list<HandlerContext>
     */
    protected array $commandStack = [];

    public function __construct()
    {
        $this->extensions = new ExtensionCollection();
    }

    /**
     * @param mixed $api
     * @param bool  $forcePrepend
     */
    public function addApi($api, $forcePrepend = false): void
    {
        @trigger_error(
            sprintf(
                'The %s method is deprecated and will be removed in 3.0. Use dependency-injection to inject the api into the action instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $forcePrepend ?
            array_unshift($this->apis, $api) :
            array_push($this->apis, $api)
        ;
    }

    /**
     * @param bool                   $forcePrepend
     */
    public function addAction(ActionInterface $action, $forcePrepend = false): void
    {
        $forcePrepend ?
            array_unshift($this->actions, $action) :
            array_push($this->actions, $action)
        ;
    }

    /**
     * @param bool                                     $forcePrepend
     */
    public function addExtension(ExtensionInterface $extension, $forcePrepend = false): void
    {
        $this->extensions->addExtension($extension, $forcePrepend);
    }

    public function getExtensions(): ExtensionCollection
    {
        return $this->extensions;
    }

    public function execute(/* CommandInterface */ $request, $catchReply = false)
    {
        if (func_num_args() > 1) {
            trigger_deprecation(
                'payum/core',
                '2.0',
                'Passing the $catchReply argument to %s is deprecated and will be removed in 3.0. Use %s::execute($request) instead.',
                __METHOD__,
                self::class
            );
        }

        if ($request instanceof CommandInterface) {
            return $this->dispatch($request);
        }

        trigger_deprecation(
            'payum/core',
            '2.0',
            'Not passing a %s instance to %s is deprecated and will not be supported in 3.0. Use %s::execute(CommandInterface $request) instead.',
            CommandInterface::class,
            __METHOD__,
            self::class
        );

        // A 1.x request is answered by the handler that means the same thing, when the gateway has one.
        //
        // Handlers are asked first on purpose. Core's own actions claim requests broadly -- a token is a
        // DetailsAggregateInterface, so ExecuteSameRequestWithModelDetailsAction supports Capture($token)
        // -- and asking actions first would swallow every request before a handler ever saw it. Asking
        // handlers first is also what a gateway part-way through moving needs: what it has ported goes to
        // a handler, and the rest falls through to the actions it still has.
        if ($this->handlerMap instanceof HandlerMap) {
            if ($request instanceof GetStatusInterface) {
                // A gateway still holding a status action reads the details of whatever has not moved,
                // which is more than the recorded status knows. Only answer from the record when nothing
                // else claims it.
                if (! $this->findActionSupported($request)) {
                    $this->answerStatus($request);

                    return null;
                }
            } elseif (RequestToCommand::supports($request)) {
                $command = RequestToCommand::translate($request, $this->resolveSubjectOf($request));

                if ($command instanceof CommandInterface && $this->supportsCommand($command::class)) {
                    return $this->replyFor($this->dispatch($command), $catchReply);
                }
            }
        }

        $context = new Context($this, $request, $this->stack);

        $this->stack[] = $context;

        try {
            $this->extensions->onPreExecute($context);

            if (! $context->getAction()) {
                if (! $action = $this->findActionSupported($context->getRequest())) {
                    throw RequestNotSupportedException::create($context->getRequest());
                }

                $context->setAction($action);
            }

            $this->extensions->onExecute($context);

            $context->getAction()->execute($request);

            $this->extensions->onPostExecute($context);

            array_pop($this->stack);
        } catch (ReplyInterface $reply) {
            $context->setReply($reply);

            $this->extensions->onPostExecute($context);

            array_pop($this->stack);

            if ($catchReply && $context->getReply()) {
                return $context->getReply();
            }

            if ($context->getReply()) {
                throw $context->getReply();
            }
        } catch (Exception $e) {
            $context->setException($e);

            $this->onPostExecuteWithException($context);
        }

        return null;
    }

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Whether this gateway has a handler for the given command.
     *
     * @param class-string<CommandInterface<Result>> $commandClass
     */
    public function supportsCommand(string $commandClass): bool
    {
        return null !== $this->handlerMap?->serviceIdFor($commandClass);
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setHandlerMap(HandlerMap $handlerMap): self
    {
        $this->handlerMap = $handlerMap;

        return $this;
    }

    protected function onPostExecuteWithException(Context $context): void
    {
        array_pop($this->stack);

        $exception = $context->getException();

        try {
            $this->extensions->onPostExecute($context);
        } catch (Exception $e) {
            // logic is similar to one in Symfony's ExceptionListener::onKernelException
            $wrapper = $e;
            while (($prev = $wrapper->getPrevious()) instanceof Throwable) {
                if ($exception === $wrapper = $prev) {
                    throw $e;
                }
            }

            $prev = new ReflectionProperty('Exception', 'previous');
            $prev->setValue($wrapper, $exception);

            throw $e;
        }

        if ($context->getException()) {
            throw $context->getException();
        }
    }

    /**
     * @param mixed $request
     *
     * @return ActionInterface|false
     */
    protected function findActionSupported($request)
    {
        foreach ($this->actions as $action) {
            if ($action instanceof GatewayAwareInterface) {
                $action->setGateway($this);
            }

            if ($action instanceof ApiAwareInterface) {
                @trigger_error(
                    sprintf(
                        'Implementing the %s interface in %s is deprecated and will be removed in 2.0. Use dependency-injection to inject the api into the action instead.',
                        ApiAwareInterface::class,
                        $action::class,
                    ),
                    E_USER_DEPRECATED
                );

                $apiSet = false;
                $unsupportedException = null;
                foreach ($this->apis as $api) {
                    try {
                        $action->setApi($api);
                        $apiSet = true;
                        break;
                    } catch (UnsupportedApiException $e) {
                        $unsupportedException = $e;
                    }
                }

                if (! $apiSet) {
                    throw new LogicException(sprintf('Cannot find right api for the action %s', $action::class), 0, $unsupportedException);
                }
            }

            if ($action->supports($request)) {
                return $action;
            }
        }

        return false;
    }

    /**
     * @param CommandInterface<Result> $command
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function dispatch(CommandInterface $command): Result
    {
        $serviceId = $this->handlerMap?->serviceIdFor($command::class);

        if (null === $serviceId) {
            throw CommandNotSupportedException::create(
                $command,
                $this->name,
                $this->container->has(PaymentGateway::class) ? $this->container->get(PaymentGateway::class) : null,
                $this->handlerMap?->commands() ?? [],
            );
        }

        $context = $this->buildContext($command);
        $this->commandStack[] = $context;

        try {
            return $this->pipeline()->process(
                $command,
                $context,
                fn (CommandInterface $c, HandlerContext $ctx): Result => $this->handle($serviceId, $c, $ctx),
            );
        } finally {
            array_pop($this->commandStack);
        }
    }

    /**
     * @param class-string<HandlerInterface> $serviceId
     * @param CommandInterface<Result> $command
     */
    private function handle(string $serviceId, CommandInterface $command, HandlerContext $context): Result
    {
        $handler = $this->container->get($serviceId);

        if (! $handler instanceof HandlerInterface || ! method_exists($handler, 'handle')) {
            throw new LogicException(sprintf('%s must be a handler declaring handle().', $serviceId));
        }

        // Only a handler wrapping a 1.x action asks for this: the action it holds still dispatches
        // GetHttpRequest and RenderTemplate at the gateway, and something has to be there to dispatch on.
        if ($handler instanceof GatewayAwareInterface) {
            $handler->setGateway($this);
        }

        if ($handler instanceof NotifyHandlerInterface) {
            if (! $command instanceof NotifyCommand) {
                throw new LogicException(sprintf(
                    '%s handles %s, but %s was dispatched.',
                    $handler::class,
                    NotifyCommand::class,
                    $command::class,
                ));
            }

            // Verification runs here rather than during dispatch so that middleware wraps it: a message
            // that fails the check is something an application will want to see.
            $result = $handler->handle($command, $handler->verify($context->httpRequest()), $context);
        } else {
            $result = $handler->handle($command, $context);
        }

        if (! $result instanceof Result) {
            throw new LogicException(sprintf('%s::handle() must return a %s.', $handler::class, Result::class));
        }

        return $result;
    }

    /**
     * Built once and reused. A sub-command dispatched from a handler comes back through the same pipeline,
     * which is what makes nesting work.
     */
    private function pipeline(): Pipeline
    {
        if ($this->pipeline instanceof Pipeline) {
            return $this->pipeline;
        }

        $collection = $this->container->has(MiddlewareCollection::class)
            ? $this->container->get(MiddlewareCollection::class)
            : new MiddlewareCollection();

        $middleware = $collection->resolve($this->container);

        foreach ($middleware as $one) {
            if ($one instanceof GatewayAwareInterface) {
                $one->setGateway($this);
            }
        }

        return $this->pipeline = new Pipeline($middleware);
    }

    /**
     * @param CommandInterface<Result> $command
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function buildContext(CommandInterface $command): HandlerContext
    {
        return new HandlerContext(
            $this,
            $command,
            $this->container->get(PaymentGateway::class),
            $this->container->get(ServerRequestInterface::class),
            $this->container->get(GenericTokenFactoryInterface::class),
            $this->resolveSubject($command),
            $command->token(),
            $this->commandStack,
            $this->name,
        );
    }

    /**
     * Tells a 1.x caller what a handler decided, the way it expects to hear it.
     *
     * Throws the matching reply when the customer has somewhere to be, or returns it when the caller
     * asked to catch it. A result with nothing left to do answers with null, the same as a 1.x action
     * returning without throwing. A RenderTemplate is rendered here, since ResultToReply has no renderer
     * to resolve it with.
     */
    private function replyFor(Result $result, bool $catchReply): ?Base
    {
        $next = $result->next;

        $reply = $next instanceof RenderTemplate
            ? new HttpResponse($this->container->get(RendererInterface::class)->render($next->template, $next->context))
            : ResultToReply::translate($result);

        if (! $reply instanceof Base) {
            return null;
        }

        if ($catchReply) {
            return $reply;
        }

        throw $reply;
    }

    /**
     * A gateway built from handlers has no status action. It has something better: the status recorded
     * after every command, which is what this reads.
     *
     * A subject that tracks no status leaves the request marked unknown, since nobody knows.
     */
    private function answerStatus(GetStatusInterface $request): void
    {
        $subject = $this->resolveSubjectOf($request);

        if ($subject instanceof SubjectInterface) {
            // 1.x actions do this, and it is what makes getFirstModel() return the payment rather than
            // the token it arrived on -- which Payum::done() relies on.
            $request->setModel($subject);
        }

        StatusMarker::mark($request, $subject instanceof SubjectInterface ? PaymentStatuses::of($subject) : null);
    }

    /**
     * The subject a 1.x request is about, from the model it carries or the token it arrived on.
     */
    private function resolveSubjectOf(object $request): ?SubjectInterface
    {
        if (! $request instanceof Generic) {
            return null;
        }

        if (($model = $request->getFirstModel()) instanceof SubjectInterface) {
            return $model;
        }

        $identity = $request->getToken()?->getDetails();

        if (null === $identity || ! $this->container->has(StorageRegistryInterface::class)) {
            return null;
        }

        $model = $this->container->get(StorageRegistryInterface::class)
            ->getStorage($identity->getClass())
            ->find($identity);

        return $model instanceof SubjectInterface ? $model : null;
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function resolveSubject(CommandInterface $command): ?SubjectInterface
    {
        if (($subject = $command->subject()) instanceof SubjectInterface) {
            return $subject;
        }

        $identity = $command->token()?->getDetails();

        if (null === $identity || ! $this->container->has(StorageRegistryInterface::class)) {
            return null;
        }

        $model = $this->container->get(StorageRegistryInterface::class)
            ->getStorage($identity->getClass())
            ->find($identity);

        return $model instanceof SubjectInterface ? $model : null;
    }
}
