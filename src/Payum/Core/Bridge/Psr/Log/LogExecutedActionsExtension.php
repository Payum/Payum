<?php
namespace Payum\Core\Bridge\Psr\Log;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Debug\Humanify;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Reply\ReplyInterface;
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
        $this->logger = $logger ?: new NullLogger();
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
            Humanify::value($action, false),
            Humanify::request($request)
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
    public function onReply(ReplyInterface $reply, $request, ActionInterface $action)
    {
        $this->logger->debug(sprintf('[Payum] %d# %s::execute(%s) throws reply %s',
            $this->stackLevel,
            Humanify::value($action),
            Humanify::request($request),
            Humanify::request($reply)
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
            $action ? Humanify::value($action) : 'Payment',
            Humanify::request($request),
            Humanify::value($exception)
        ));

        $this->stackLevel--;
    }
}
