<?php

namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class LoggerAwareAction implements ActionInterface, LoggerAwareInterface
{
    protected $logger;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function execute($request)
    {
        if ($this->logger) {
            $this->logger->debug('I can log something here');
        }
    }

    public function supports($request)
    {
        return $request == 'a request';
    }
}
