<?php
namespace Payum\Core\Bridge\Psr\Log;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggerExtension implements ExtensionInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var NullLogger
     */
    protected $nullLogger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->nullLogger = new NullLogger();
        $this->logger = $logger ?: $this->nullLogger;
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
    public function onPreExecute(Context $context)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context)
    {
        $action = $context->getAction();
        if ($action instanceof LoggerAwareInterface) {
            $action->setLogger($this->logger);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context)
    {
        $action = $context->getAction();
        if ($action instanceof LoggerAwareInterface) {
            $action->setLogger($this->nullLogger);
        }
    }
}
