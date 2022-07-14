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

class Gateway implements GatewayInterface
{
    /**
     * @var Action\ActionInterface[]
     */
    protected array $actions = [];

    /**
     * @var object[]
     */
    protected array $apis = [];

    protected ExtensionCollection $extensions;

    /**
     * @var Context[]
     */
    protected array $stack = [];

    public function __construct()
    {
        $this->extensions = new ExtensionCollection();
    }

    public function addApi(object $api, bool $forcePrepend = false): void
    {
        $forcePrepend ?
            array_unshift($this->apis, $api) :
            array_push($this->apis, $api)
        ;
    }

    public function addAction(ActionInterface $action, bool $forcePrepend = false): void
    {
        $forcePrepend ?
            array_unshift($this->actions, $action) :
            array_push($this->actions, $action)
        ;
    }

    public function addExtension(ExtensionInterface $extension, bool $forcePrepend = false): void
    {
        $this->extensions->addExtension($extension, $forcePrepend);
    }

    public function execute(mixed $request, bool $catchReply = false): ?ReplyInterface
    {
        $context = new Context($this, $request, $this->stack);

        $this->stack[] = $context;

        try {
            $this->extensions->onPreExecute($context);

            if (! $context->getAction()) {
                $action = $this->findActionSupported($context->getRequest());
                if (! $action) {
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

    /**
     * @throws Exception
     */
    protected function onPostExecuteWithException(Context $context): void
    {
        array_pop($this->stack);

        $exception = $context->getException();

        try {
            $this->extensions->onPostExecute($context);
        } catch (Exception $e) {
            // logic is similar to one in Symfony's ExceptionListener::onKernelException
            $wrapper = $e;
            while ($prev = $wrapper->getPrevious()) {
                $wrapper = $prev;
                if ($exception === $wrapper) {
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

    protected function findActionSupported(mixed $request): ?ActionInterface
    {
        foreach ($this->actions as $action) {
            if ($action instanceof GatewayAwareInterface) {
                $action->setGateway($this);
            }

            if ($action instanceof ApiAwareInterface) {
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
                    throw new LogicException(sprintf('Cannot find right api for the action %s', get_class($action)), 0, $unsupportedException);
                }
            }

            if ($action->supports($request)) {
                return $action;
            }
        }

        return null;
    }
}
