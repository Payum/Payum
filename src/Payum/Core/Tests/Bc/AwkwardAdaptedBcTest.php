<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use Payum\Core\Command\CommandInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\Result;
use Payum\Core\Tests\Bc\Fixture\AwkwardAdaptedConfig;

/**
 * Actions answering commands through ActionToHandlerAdapter, beside a handler that was ported. Commands on a
 * token go to the gateway itself, not through LegacyGatewayAdapter.
 */
final class AwkwardAdaptedBcTest extends BcTestCase
{
    protected static function scenarios(): string
    {
        return 'awkward_mixed';
    }

    protected static function register(): callable
    {
        return static function (PayumBuilder $builder): void {
            $builder->registerGateway(Recorder::GATEWAY, new AwkwardAdaptedConfig());
        };
    }

    protected static function executeCommand(GatewayInterface $gateway, CommandInterface $command): Result
    {
        // A payment handed to a command directly is the caller's to persist on 2.x, which 1.x did for it.
        if (null === $command->token()) {
            return parent::executeCommand($gateway, $command);
        }

        return $gateway->execute($command);
    }
}
