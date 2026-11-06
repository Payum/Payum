<?php

declare(strict_types=1);

namespace Payum\Offline;

use League\Uri\Uri;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresActions;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\DeclaresMiddleware;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Path;
use Payum\Offline\Action\StatusAction;
use Payum\Offline\Handler\AuthorizeHandler;
use Payum\Offline\Handler\CancelHandler;
use Payum\Offline\Handler\CaptureHandler;
use Payum\Offline\Handler\PayoutHandler;
use Payum\Offline\Handler\RefundHandler;
use Payum\Offline\Handler\SyncHandler;
use Payum\Offline\Middleware\StatusToDetailsMiddleware;
use Psr\Container\ContainerInterface;
use function get_debug_type;
use function sprintf;

class OfflineGateway implements GatewayInterface, DeclaresActions, DeclaresCapabilities, DeclaresMiddleware, ContainerConfiguration
{
    public function name(): string
    {
        return 'Offline';
    }

    public function logo(): Logo
    {
        return Path::create(__DIR__ . '/Resources/logo.svg');
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://payum.dev/docs/offline');
    }

    public function configClass(): string
    {
        return OfflineConfig::class;
    }

    public function handlers(): array
    {
        return [
            CaptureHandler::class,
            AuthorizeHandler::class,
            RefundHandler::class,
            CancelHandler::class,
            SyncHandler::class,
            PayoutHandler::class,
        ];
    }

    public function capabilities(): array
    {
        return [Capability::PartialRefund, Capability::MultiCurrency];
    }

    public function middleware(): array
    {
        return [StatusToDetailsMiddleware::class];
    }

    /**
     * Answers 1.x status requests for payments captured before the upgrade, which have no recorded status.
     */
    public function actions(): array
    {
        return [StatusAction::class];
    }

    public function configureContainer(): array
    {
        // Handlers type-hint OfflineConfig, but the container only knows the concrete class of the config
        // the application registered, which may extend it.
        $definitions = [
            OfflineConfig::class => self::config(...),
        ];

        foreach (HandlerMap::fromHandlers($this->handlers())->bindings() as $interface => $default) {
            $definitions[$interface] = static function (ContainerInterface $container) use ($interface, $default): HandlerInterface {
                $handler = self::config($container)->handlers[$interface] ?? $default;

                return $handler instanceof HandlerInterface ? $handler : $container->get($handler);
            };
        }

        return $definitions;
    }

    private static function config(ContainerInterface $container): OfflineConfig
    {
        $config = $container->get(GatewayConfig::class);

        if (! $config instanceof OfflineConfig) {
            throw new LogicException(sprintf('The Offline gateway needs an %s, but %s was registered.', OfflineConfig::class, get_debug_type($config)));
        }

        return $config;
    }
}
