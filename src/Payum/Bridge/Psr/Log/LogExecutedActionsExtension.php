<?php
namespace Payum\Extension;

use Payum\Action\ActionInterface;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\ModelRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LogExecutedActionsExtension implements ExtensionInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $stackLevel;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger;
        $this->stackLevel = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute($request)
    {
        $this->logger->debug('[Payum][Stack %d] Pre execute: %s', array(
            $this->stackLevel,
            $this->convertRequestToLogMessage($request)
        ));

        $this->stackLevel++;
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        $ro = new \ReflectionObject($action);

        $this->logger->debug('[Payum][Stack %d] Execute: %s(%s)', array(
            $this->stackLevel,
            $ro->getShortName(),
            $this->convertRequestToLogMessage($request)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        $ro = new \ReflectionObject($action);

        $this->logger->debug('[Payum][Stack %d] Post execute: %s(%s)', array(
            $this->stackLevel,
            $ro->getShortName(),
            $this->convertRequestToLogMessage($request)
        ));

        $this->stackLevel--;
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        $ro = new \ReflectionObject($action);

        $this->logger->debug('[Payum][Stack %d] Action %s throws interactive %s request', array(
            $this->stackLevel,
            $ro->getShortName(),
            $this->convertRequestToLogMessage($request)
        ));

        $this->stackLevel--;
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        $this->stackLevel--;

        $this->logger->error('[Payum][Stack %d] An exception %s was thrown on line: %s in file: ', array(
            $this->stackLevel,
            get_class($exception),
            $exception->getLine(),
            $exception->getFile(),
        ));
    }

    /**
     * @param mixed $request
     * @return string
     */
    protected function convertRequestToLogMessage($request)
    {
        if (false == is_object($request)) {
            return gettype($request);
        }

        $ro = new \ReflectionObject($request);

        $message = $ro->getShortName();
        if ($request instanceof ModelRequestInterface) {
            $model = $request->getModel();

            $message .= sprintf("{%s}", is_object($model) ? get_class($model) : gettype($model));
        }

        return $message;
    }
}