<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use Payum\Core\Command\SyncCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\SyncHandlerInterface;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\SyncResult;

/**
 * {@see AwkwardSyncAction}, ported.
 */
final class AwkwardSyncHandler implements SyncHandlerInterface
{
    public function handle(SyncCommand $command, Context $context): SyncResult
    {
        $state = $context->state();
        $state['synced'] = true;

        if ('pending' === $state['status']) {
            $state['status'] = 'captured';
        }

        return SyncResult::synced(PaymentStatus::tryFrom((string) $state['status']) ?? PaymentStatus::Unknown);
    }
}
