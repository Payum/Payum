<?php

namespace Payum\Core;

use Exception;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Exception\CommandNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Handler\Context as HandlerContext;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Result\Result;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
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
            throw CommandNotSupportedException::create($command);
        }

        $handler = $this->container->get($serviceId);

        if (! $handler instanceof HandlerInterface || ! method_exists($handler, 'handle')) {
            throw new LogicException(sprintf('%s must be a handler declaring handle().', $serviceId));
        }

        $context = $this->buildContext($command);
        $this->commandStack[] = $context;

        try {
            $result = $handler->handle($command, $context);

            if (! $result instanceof Result) {
                throw new LogicException(sprintf('%s::handle() must return a %s.', $handler::class, Result::class));
            }

            return $result;
        } finally {
            array_pop($this->commandStack);

            // In finally rather than on success: a PSP token written before a later failure still has to
            // survive, or the retry opens a second checkout.
            $this->persistState($command, $context);
        }
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
            $this->container->get(\Payum\Core\Gateway\GatewayInterface::class),
            $this->container->get(ServerRequestInterface::class),
            $this->container->get(GenericTokenFactoryInterface::class),
            $this->resolvePayment($command),
            $command->token(),
            $this->commandStack,
        );
    }

    /**
     * @param CommandInterface<Result> $command
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolvePayment(CommandInterface $command): ?PaymentInterface
    {
        if (($payment = $command->payment()) instanceof PaymentInterface) {
            return $payment;
        }

        $identity = $command->token()?->getDetails();

        if (null === $identity || ! $this->container->has(StorageRegistryInterface::class)) {
            return null;
        }

        $model = $this->container->get(StorageRegistryInterface::class)
            ->getStorage($identity->getClass())
            ->find($identity);

        return $model instanceof PaymentInterface ? $model : null;
    }

    /**
     * @param CommandInterface<Result> $command
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function persistState(CommandInterface $command, HandlerContext $context): void
    {
        $payment = $context->payment();
        $state = $context->pendingState();

        if (! $payment instanceof PaymentInterface || ! $state instanceof ArrayObject) {
            return;
        }

        $payment->setDetails($state);

        // Core writes back only what it loaded. A payment handed to the command directly belongs to the
        // caller, who persists it on their own terms.
        if (! $command->token() instanceof TokenInterface || ! $this->container->has(StorageRegistryInterface::class)) {
            return;
        }

        $this->container->get(StorageRegistryInterface::class)
            ->getStorage($payment::class)
            ->update($payment);
    }
}
