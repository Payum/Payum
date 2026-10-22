<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\PrependActionInterface;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Gateway;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Result\Result;
use function in_array;
use function sprintf;

/**
 * Puts a handler into a gateway that a 1.x factory still builds, as one of its actions:
 *
 *     'payum.action.refund' => new HandlerToActionAdapter(new RefundHandler($api)),
 *
 * It answers the 1.x request that means the handler's command, and the handler runs through the gateway's
 * middleware as it would on a gateway built from handlers. It goes ahead of core's own actions, which would
 * otherwise swap the payment for its details before the handler saw it.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class HandlerToActionAdapter implements ActionInterface, GatewayAwareInterface, PrependActionInterface
{
    use GatewayAwareTrait;

    /**
     * @var list<class-string<CommandInterface<Result>>>
     */
    private readonly array $commands;

    public function __construct(
        private readonly HandlerInterface $handler,
    ) {
        $this->commands = HandlerMap::fromHandlers([$handler::class])->commands();
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if (! $this->gateway instanceof Gateway) {
            throw new LogicException(sprintf(
                '%s runs %s through the gateway executing it, which has to be a %s. %s was given.',
                self::class,
                $this->handler::class,
                Gateway::class,
                get_debug_type($this->gateway),
            ));
        }

        /** @var Generic $request */
        /** @var SubjectInterface $subject */
        $subject = $request->getModel();

        /** @var CommandInterface<Result> $command */
        $command = RequestToCommand::translate($request, $subject);

        $this->gateway->executeHandler($this->handler, $command, $subject);
    }

    /**
     * Claimed once the request carries the payment itself. Until then core's own actions are still loading it
     * from the token, and a handler needs it loaded.
     */
    public function supports($request): bool
    {
        if (! $request instanceof Generic || ! ($subject = $request->getModel()) instanceof SubjectInterface) {
            return false;
        }

        $command = RequestToCommand::translate($request, $subject);

        return null !== $command && in_array($command::class, $this->commands, true);
    }
}
