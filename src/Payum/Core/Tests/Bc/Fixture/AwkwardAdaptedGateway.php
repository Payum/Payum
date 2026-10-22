<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use League\Uri\Uri;
use Payum\Core\Gateway\DeclaresActions;
use Payum\Core\Gateway\DeclaresTemplates;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Legacy\Handler\AuthorizeActionHandler;
use Payum\Core\Legacy\Handler\CaptureActionHandler;
use Payum\Core\Legacy\Handler\NotifyActionHandler;
use Payum\Core\Legacy\Handler\RefundActionHandler;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;

/**
 * The awkward gateway answering every command: sync with the handler it was ported to, everything else with
 * the 1.x action it still has. 2.x only; record.php never loads it.
 */
final class AwkwardAdaptedGateway implements GatewayInterface, DeclaresActions, DeclaresTemplates
{
    public function configClass(): string
    {
        return AwkwardAdaptedConfig::class;
    }

    public function handlers(): array
    {
        return [
            CaptureActionHandler::class,
            AuthorizeActionHandler::class,
            RefundActionHandler::class,
            NotifyActionHandler::class,
            AwkwardSyncHandler::class,
        ];
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
