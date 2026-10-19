<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use League\Uri\Uri;
use Payum\Core\Gateway\DeclaresActions;
use Payum\Core\Gateway\DeclaresTemplates;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;

/**
 * The awkward gateway part-way through moving: sync is a handler, everything else is still the 1.x action.
 * 2.x only; record.php never loads it.
 */
final class AwkwardMixedGateway implements GatewayInterface, DeclaresActions, DeclaresTemplates
{
    public function configClass(): string
    {
        return AwkwardMixedConfig::class;
    }

    public function handlers(): array
    {
        return [AwkwardSyncHandler::class];
    }

    public function actions(): array
    {
        return [
            AwkwardCaptureAction::class,
            AwkwardRedirectAction::class,
            AwkwardAuthorizeAction::class,
            AwkwardRefundAction::class,
            AwkwardNotifyAction::class,
            AwkwardStatusAction::class,
            AwkwardConvertPaymentAction::class,
        ];
    }

    public function templateNamespaces(): array
    {
        return [
            'AwkwardBc' => __DIR__ . '/views',
        ];
    }

    public function logo(): Logo
    {
        return Url::create('https://psp.test/logo.svg');
    }

    public function name(): string
    {
        return 'Awkward';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://psp.test');
    }
}
