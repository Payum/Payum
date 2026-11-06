<?php

declare(strict_types=1);

namespace Payum\Offline;

use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Handler\HandlerMap;
use function array_keys;
use function implode;
use function is_string;
use function sprintf;

class OfflineConfig implements GatewayConfig
{
    /**
     * @var array<class-string<HandlerInterface>, HandlerInterface|class-string<HandlerInterface>>
     */
    public readonly array $handlers;

    /**
     * @param bool $paid whether a payment counts as paid as soon as it is captured or authorized. A payment's
     *                   own `paid` detail, when set, wins
     * @param list<HandlerInterface|class-string<HandlerInterface>> $handlers each replaces the gateway's handler
     *                                                                      for the command it handles. A class
     *                                                                      name is autowired
     */
    public function __construct(
        public readonly bool $paid = true,
        array $handlers = [],
    ) {
        $gatewayClass = $this->getGatewayClass();
        $shipped = HandlerMap::fromHandlers((new $gatewayClass())->handlers())->bindings();
        $replacements = [];

        foreach ($handlers as $handler) {
            $class = is_string($handler) ? $handler : $handler::class;

            foreach (array_keys(HandlerMap::fromHandlers([$class])->bindings()) as $interface) {
                if (! isset($shipped[$interface])) {
                    throw new InvalidArgumentException(sprintf(
                        '%s implements %s, but %s only takes the commands of %s.',
                        $class,
                        $interface,
                        $gatewayClass,
                        implode(', ', array_keys($shipped)),
                    ));
                }

                if (isset($replacements[$interface])) {
                    throw new InvalidArgumentException(sprintf(
                        'Both %s and %s implement %s. Give one handler per command.',
                        is_string($replacements[$interface]) ? $replacements[$interface] : $replacements[$interface]::class,
                        $class,
                        $interface,
                    ));
                }

                $replacements[$interface] = $handler;
            }
        }

        $this->handlers = $replacements;
    }

    public function getGatewayClass(): string
    {
        return OfflineGateway::class;
    }
}
