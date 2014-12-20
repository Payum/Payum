<?php
namespace Payum\Core\Bridge\Psr\Log;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Reply\ReplyInterface;
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
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
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
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        if ($action instanceof LoggerAwareInterface) {
            $action->setLogger($this->logger);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onReply(ReplyInterface $reply, $request, ActionInterface $action)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
    }
}
