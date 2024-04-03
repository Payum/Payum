<?php

namespace Payum\Core;

use Exception;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Reply\ReplyInterface;
use ReflectionProperty;
use Throwable;

class Gateway implements GatewayInterface
{
    /**
     * @var Action\ActionInterface[]
     */
    protected $actions = [];

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
    protected $stack = [];

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

    public function execute($request, $catchReply = false)
    {
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

        return;
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
            $prev->setAccessible(true);
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
}
