<?php

declare(strict_types=1);

namespace Payum\Skeleton;

use League\Uri\Uri;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Path;
use Payum\Skeleton\Handler\AuthorizeHandler;
use Payum\Skeleton\Handler\CancelHandler;
use Payum\Skeleton\Handler\CaptureHandler;
use Payum\Skeleton\Handler\NotifyHandler;
use Payum\Skeleton\Handler\RefundHandler;

final class SkeletonGateway implements GatewayInterface
{
    public function name(): string
    {
        return 'Skeleton';
    }

    public function logo(): Logo
    {
        return Path::create(__DIR__ . '/Resources/logo.svg');
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://example.com');
    }

    public function configClass(): string
    {
        return SkeletonConfig::class;
    }

    public function handlers(): array
    {
        return [
            CaptureHandler::class,
            AuthorizeHandler::class,
            RefundHandler::class,
            CancelHandler::class,
            NotifyHandler::class,
        ];
    }
}
