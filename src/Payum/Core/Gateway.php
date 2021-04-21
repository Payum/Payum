<?php
namespace Payum\Core;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Reply\ReplyInterface;

class Gateway implements GatewayInterface
{
    /**
     * @var Action\ActionInterface[]
     */
    protected $actions;

    /**
     * @var mixed[]
     */
    protected $apis;

    /**
     * @var \Payum\Core\Extension\ExtensionCollection
     */
    protected $extensions;

    /**
     * @var Context[]
     */
    protected $stack;

    /**
     */
    public function __construct()
    {
        $this->stack = [];
        $this->actions = [];
        $this->apis = [];

        $this->extensions = new ExtensionCollection();
    }

    /**
     * @param mixed $api
     * @param bool  $forcePrepend
     *
     * @return void
     */
    public function addApi($api, $forcePrepend = false)
    {
        $forcePrepend ?
            array_unshift($this->apis, $api) :
            array_push($this->apis, $api)
        ;
    }

    /**
     * @param Action\ActionInterface $action
     * @param bool                   $forcePrepend
     *
     * @return void
     */
    public function addAction(ActionInterface $action, $forcePrepend = false)
    {
        $forcePrepend ?
            array_unshift($this->actions, $action) :
            array_push($this->actions, $action)
        ;
    }

    /**
     * @param \Payum\Core\Extension\ExtensionInterface $extension
     * @param bool                                     $forcePrepend
     *
     * @return void
     */
    public function addExtension(ExtensionInterface $extension, $forcePrepend = false)
    {
        $this->extensions->addExtension($extension, $forcePrepend);
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request, $catchReply = false)
    {
        $context = new Context($this, $request, $this->stack);

        array_push($this->stack, $context);

        try {
            $this->extensions->onPreExecute($context);

            if (false == $context->getAction()) {
                if (false == $action = $this->findActionSupported($context->getRequest())) {
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
        } catch (\Exception $e) {
            $context->setException($e);

            $this->onPostExecuteWithException($context);
        }

        return;
    }

    protected function onPostExecuteWithException(Context $context)
    {
        array_pop($this->stack);

        $exception = $context->getException();

        try {
            $this->extensions->onPostExecute($context);
        } catch (\Exception $e) {
            // logic is similar to one in Symfony's ExceptionListener::onKernelException
            $wrapper = $e;
            while ($prev = $wrapper->getPrevious()) {
                if ($exception === $wrapper = $prev) {
                    throw $e;
                }
            }

            $prev = new \ReflectionProperty('Exception', 'previous');
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

                if (false == $apiSet) {
                    throw new LogicException(sprintf('Cannot find right api for the action %s', get_class($action)), null, $unsupportedException);
                }
            }

            if ($action->supports($request)) {
                return $action;
            }
        }

        return false;
    }
}
