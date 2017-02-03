# Logging

Since we are dealing with payments it is required to log sensitive details. if the problem appear it would be easy to find out the problem when you have a good log file. This lib provide support of [PSR-3 compatible loggers](http://www.php-fig.org/psr/psr-3/).

To inject a logger you have to create a logger itself, and add an extension with that logger to a gateway.

```php
<?php
use Payum\Core\Bridge\Psr\Log\LoggerExtension;
use Payum\Core\Tests\Mocks\Action\LoggerAwareAction;
use Payum\Core\Gateway;

/** @var \Psr\Log\LoggerInterface $logger */

$gateway = new Gateway;
$gateway->addExtension(new LoggerExtension($logger));
$gateway->addAction(new LoggerAwareAction);

$gateway->execute('a request');
```

After you are done you can simply implement `LoggerAwareInterface` interface to an action where you want log something. It will be injected by the extension.

```php
<?php
namespace App\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class LoggerAwareAction implements ActionInterface, LoggerAwareInterface
{
    /** @var \Psr\Log\LoggerInterface $logger */
    protected $logger;

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
    public function execute($request)
    {
        if ($this->logger) {
            $this->logger->debug('I can log something here');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request == 'a request';
    }
}
```

Back to [index](index.md).