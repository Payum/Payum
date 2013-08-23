<?php
namespace Payum\Bridge\Psr\Log;

use Payum\Action\ActionInterface;
use Payum\Extension\ExtensionInterface;
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
        $this->stackLevel++;
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        $this->logger->debug(sprintf(
            '[Payum][%d] %s::execute(%s)',
            $this->stackLevel,
            $this->toString($action),
            $this->toStringRequest($request)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        $this->stackLevel--;
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        $this->logger->debug('[Payum][%d] %s::execute() throws interactive request %s', array(
            $this->stackLevel,
            $this->toString($action),
            $this->toString($interactiveRequest)
        ));

        $this->stackLevel--;
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        $this->stackLevel--;

        if ($action) {
            $this->logger->debug('[Payum][%d] %s::execute() throws exception %s', array(
                $this->stackLevel,
                $this->toString($action),
                $this->toString($exception)
            ));
        } else {
            $this->logger->debug('[Payum][%d] An exception %s is thrown. Request %s', array(
                $this->stackLevel,
                $this->toString($exception),
                $this->toString($request)
            ));
        }
    }

    /**
     * @param mixed $request
     * @return string
     */
    protected function toStringRequest($request)
    {
        $message = $this->toString($request);
        if ($request instanceof ModelRequestInterface) {
            $message .= sprintf("{%s}", $this->toString($request->getModel()));
        }

        return $message;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function toString($value)
    {
        if (is_object($value)) {
            $ro = new \ReflectionObject($value);

            return $ro->getShortName().'@'.spl_object_hash($value);
        }

        return gettype($value);
    }
}