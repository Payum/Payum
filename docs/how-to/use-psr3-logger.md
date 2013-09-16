# How to use PSR-3 Logger

You can use any PSR-3 compatible logger inside an action.
Two rules should be completed. First add `LoggerExtension` and second an action must implement `LoggerAwareInterface` interface.

```php
<?php
//Source: Payum\Examples\ReadmeTest::loggerExtension()
use Payum\Bridge\Psr\Log\LoggerExtension;
use Payum\Examples\Action\LoggerAwareAction;
use Payum\Payment;


$payment = new Payment;
$payment->addExtension(new LoggerExtension($logger));
$payment->addAction(new LoggerAwareAction);

$payment->execute('a request');
```

```php
<?php
namespace Payum\Examples\Action;

use Payum\Action\ActionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class LoggerAwareAction implements ActionInterface, LoggerAwareInterface
{
    protected $logger;

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        if ($this->logger) {
            $this->logger->debug('I can log something here');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request == 'a request';
    }
}
```

Back to [index](../index.md).