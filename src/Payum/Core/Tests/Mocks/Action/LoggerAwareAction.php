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

    public function execute(mixed $request): void
    {
        if ($this->logger) {
            $this->logger->debug('I can log something here');
        }
    }

    public function supports(mixed $request): bool
    {
        return 'a request' == $request;
    }
}
