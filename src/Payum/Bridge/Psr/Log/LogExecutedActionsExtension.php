<?php
namespace Payum\Bridge\Psr\Log;

use Payum\Action\ActionInterface;
use Payum\Extension\ExtensionInterface;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\ModelRequestInterface;
use Payum\Request\RedirectUrlInteractiveRequest;
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
            '[Payum] %d# %s::execute(%s)',
            $this->stackLevel,
            $this->toString($action, false),
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
        $this->logger->debug(sprintf('[Payum] %d# %s::execute(%s) throws interactive %s',
            $this->stackLevel,
            $this->toString($action),
            $this->toStringRequest($request),
            $this->toStringRequest($interactiveRequest)
        ));

        $this->stackLevel--;
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        $this->logger->debug(sprintf('[Payum] %d# %s::execute(%s) throws exception %s',
            $this->stackLevel,
            $action ? $this->toString($action) : 'Payment',
            $this->toStringRequest($request),
            $this->toString($exception)
        ));

        $this->stackLevel--;
    }

    /**
     * @param mixed $request
     * @return string
     */
    protected function toStringRequest($request)
    {
        $message = $this->toString($request);
        if ($request instanceof ModelRequestInterface) {
            $message .= sprintf("{model: %s}", $this->toString($request->getModel()));
        }
        if ($request instanceof RedirectUrlInteractiveRequest) {
            $message .= sprintf('{url: %s}', $request->getUrl());
        }

        return $message;
    }

    /**
     * @param mixed $value
     * @param bool $shortClass
     *
     * @return string
     */
    protected function toString($value, $shortClass = true)
    {
        if (is_object($value)) {
            if ($shortClass) {
                $ro = new \ReflectionObject($value);

                return $ro->getShortName();
            }

            return get_class($value);
        }

        return gettype($value);
    }
}