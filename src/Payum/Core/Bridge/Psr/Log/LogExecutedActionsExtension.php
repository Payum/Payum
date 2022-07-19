<?php

namespace Payum\Core\Bridge\Psr\Log;

use Payum\Core\Debug\Humanify;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LogExecutedActionsExtension implements ExtensionInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function onPreExecute(Context $context): void
    {
    }

    public function onExecute(Context $context): void
    {
        $this->logger->debug(sprintf(
            '[Payum] %d# %s::execute(%s)',
            count($context->getPrevious()) + 1,
            Humanify::value($context->getAction(), false),
            Humanify::request($context->getRequest())
        ));
    }

    public function onPostExecute(Context $context): void
    {
        if ($context->getReply()) {
            $this->logger->debug(sprintf(
                '[Payum] %d# %s::execute(%s) throws reply %s',
                count($context->getPrevious()) + 1,
                Humanify::value($context->getAction()),
                Humanify::request($context->getRequest()),
                Humanify::request($context->getReply())
            ));
        } elseif ($context->getException()) {
            $this->logger->debug(sprintf(
                '[Payum] %d# %s::execute(%s) throws exception %s',
                count($context->getPrevious()) + 1,
                $context->getAction() ? Humanify::value($context->getAction()) : 'Gateway',
                Humanify::request($context->getRequest()),
                Humanify::value($context->getException())
            ));
        }
    }
}
