<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\PayoutCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Exception\CommandNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\AuthorizeResult;
use Payum\Core\Result\CancelResult;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\PayoutResult;
use Payum\Core\Result\RefundResult;
use Payum\Core\Result\Result;
use Payum\Core\Result\SyncResult;
use function array_filter;
use function array_keys;
use function array_values;
use function count;
use function func_num_args;
use function implode;
use function sprintf;

/**
 * Drives a 1.x gateway -- a factory and its actions -- with 2.0 commands.
 *
 * The other direction already works: {@see RequestToCommand} lets an application keep dispatching 1.x
 * requests at a gateway that has moved to handlers. This is the half that matters for adoption. Without
 * it, an application that starts dispatching commands can only talk to gateways that have been ported,
 * so every third-party package that has not moved yet becomes unreachable the moment its user modernises.
 *
 * Wrap a gateway the registry built and dispatch commands at it:
 *
 *     $gateway = LegacyGatewayAdapter::wrap($payum->getGateway('paypal_express_checkout'));
 *     $result = $gateway->execute(CaptureCommand::forToken($token));
 *
 * Anything that is not a command is passed straight through, so the wrapper is safe to keep in front of a
 * gateway an application still dispatches 1.x requests at.
 *
 * **What a 1.x gateway cannot tell you.** 1.x has no portable vocabulary for a transaction id, an amount,
 * or why something was declined, so {@see Result::$transactionId}, the per-result amounts and
 * {@see Result::$failure} are always null here. {@see Result::isFailed()} still answers, because it reads
 * the status. Read {@see Result::$status} and, for anything else, the gateway's own details array.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class LegacyGatewayAdapter implements GatewayInterface
{
    /**
     * @var array<class-string<CommandInterface<Result>>, class-string<Generic>>
     */
    private const REQUESTS = [
        CaptureCommand::class => Capture::class,
        AuthorizeCommand::class => Authorize::class,
        RefundCommand::class => Refund::class,
        CancelCommand::class => Cancel::class,
        PayoutCommand::class => Payout::class,
        SyncCommand::class => Sync::class,
        NotifyCommand::class => Notify::class,
    ];

    /**
     * NotifyCommand is absent on purpose: {@see NotifyResult} takes an acknowledgement where the others
     * take a next action, so it is built by hand.
     *
     * @var array<class-string<CommandInterface<Result>>, class-string<Result>>
     */
    private const RESULTS = [
        CaptureCommand::class => CaptureResult::class,
        AuthorizeCommand::class => AuthorizeResult::class,
        RefundCommand::class => RefundResult::class,
        CancelCommand::class => CancelResult::class,
        PayoutCommand::class => PayoutResult::class,
        SyncCommand::class => SyncResult::class,
    ];

    public function __construct(
        private readonly Gateway $gateway,
    ) {
    }

    /**
     * Takes what {@see \Payum\Core\Payum::getGateway()} hands back, which is typed to the executor
     * interface, and says so plainly when that is something other than a gateway Payum assembled.
     */
    public static function wrap(GatewayInterface $gateway): self
    {
        if (! $gateway instanceof Gateway) {
            throw new LogicException(sprintf(
                '%s reads the actions a gateway registered, so it can only wrap a %s. %s was given.',
                self::class,
                Gateway::class,
                $gateway::class,
            ));
        }

        return new self($gateway);
    }

    /**
     * Best effort, and worth saying plainly: this reports which commands the gateway has an action that
     * *claims*, which is not the same as which operations work. An action claiming Refund may still refuse
     * the refund the PSP was asked for. Treat it as a list to render in an admin screen, not as a promise.
     *
     * @return list<Capability>
     */
    public function capabilities(): array
    {
        $capabilities = [];

        foreach ($this->commands() as $commandClass) {
            $capabilities[] = $commandClass::capability();
        }

        return $capabilities;
    }

    /**
     * @param CommandInterface<Result>|mixed $request
     * @param bool $catchReply
     *
     * @return ($request is CommandInterface<Result> ? Result : ReplyInterface|null)
     */
    public function execute($request, $catchReply = false)
    {
        if ($request instanceof CommandInterface) {
            return $this->dispatch($request);
        }

        // Forwarded with one argument when the caller passed one. The second is deprecated, and passing it
        // on unasked would report this class's own call as the caller's mistake.
        return 1 === func_num_args()
            ? $this->gateway->execute($request)
            : $this->gateway->execute($request, $catchReply);
    }

    /**
     * @param class-string<CommandInterface<Result>> $commandClass
     */
    public function supportsCommand(string $commandClass): bool
    {
        $requestClass = self::REQUESTS[$commandClass] ?? null;

        // Probed with a bare details array rather than a payment: core's own actions claim a Capture
        // carrying a payment, so probing with one would report every gateway as capable of everything.
        // A 1.x gateway's own actions are the ones that match on the details.
        return null !== $requestClass && $this->gateway->supportsRequest(new $requestClass(new ArrayObject()));
    }

    private function acknowledgementFrom(?ReplyInterface $reply): ?Acknowledgement
    {
        if (! $reply instanceof HttpResponse) {
            return null;
        }

        /** @var array<string, string> $headers */
        $headers = $reply->getHeaders();

        return new Acknowledgement($reply->getStatusCode(), $reply->getContent(), $headers);
    }

    /**
     * 1.x requests carry intent and nothing else. Silently dropping an amount would refund everything
     * instead of half; silently dropping an idempotency key would let a retry charge twice.
     *
     * @param CommandInterface<Result> $command
     */
    private function assertNothingIsLost(CommandInterface $command): void
    {
        $carried = match (true) {
            $command instanceof CaptureCommand, $command instanceof AuthorizeCommand => [
                'an amount' => $command->amount,
                'an idempotency key' => $command->idempotencyKey,
            ],
            $command instanceof RefundCommand => [
                'an amount' => $command->amount,
                'a reason' => $command->reason,
                'an idempotency key' => $command->idempotencyKey,
            ],
            $command instanceof CancelCommand => [
                'a reason' => $command->reason,
                'an idempotency key' => $command->idempotencyKey,
            ],
            $command instanceof PayoutCommand => [
                'an idempotency key' => $command->idempotencyKey,
            ],
            default => [],
        };

        $lost = array_keys(array_filter($carried, static fn ($value): bool => null !== $value));

        if ([] === $lost) {
            return;
        }

        throw new LogicException(sprintf(
            '%s carries %s, and a 1.x request has nowhere to put %s. Dispatch the command without %s, or port the gateway to handlers.',
            $command::class,
            implode(' and ', $lost),
            1 === count($lost) ? 'it' : 'them',
            1 === count($lost) ? 'it' : 'them',
        ));
    }

    /**
     * @return list<class-string<CommandInterface<Result>>>
     */
    private function commands(): array
    {
        return array_values(array_filter(
            array_keys(self::REQUESTS),
            $this->supportsCommand(...),
        ));
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function dispatch(CommandInterface $command): Result
    {
        $requestClass = self::REQUESTS[$command::class] ?? null;

        if (null === $requestClass) {
            throw CommandNotSupportedException::create($command, $this->gateway->getName(), null, $this->commands());
        }

        $this->assertNothingIsLost($command);

        // The token is passed through untouched when there is one, and that is what keeps a redirect flow
        // working: the 1.x action hands the PSP the token's own URL as the return URL, so the customer
        // comes back to the page that dispatched this command and the same command runs again.
        $target = $command->token() ?? $command->subject();

        // A notify may point at nothing at all. The 1.x actions that answer one match on a details array,
        // which is what they get.
        $request = new $requestClass($target ?? new ArrayObject());
        $reply = $this->run($request, $command);
        $status = null === $target ? null : $this->statusOf($target);

        if ($command instanceof NotifyCommand) {
            return new NotifyResult($status, acknowledgement: $this->acknowledgementFrom($reply));
        }

        $resultClass = self::RESULTS[$command::class];

        return new $resultClass($status, ReplyToNextAction::translate($reply));
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function run(Generic $request, CommandInterface $command): ?ReplyInterface
    {
        try {
            $this->gateway->execute($request);

            return null;
        } catch (ReplyInterface $reply) {
            return $reply;
        } catch (RequestNotSupportedException $exception) {
            // Only when the request we built is the one nothing claimed. A sub-request an action dispatched
            // -- GetHttpRequest, Convert -- going unanswered is that gateway's own wiring problem, and
            // reporting it as "this gateway cannot capture" would send the reader to the wrong place.
            if ($exception->getRequest() !== $request) {
                throw $exception;
            }

            throw CommandNotSupportedException::create(
                $command,
                $this->gateway->getName(),
                null,
                $this->commands(),
            );
        }
    }

    /**
     * 1.x reports a status by answering GetHumanStatus rather than by returning one, so it has to be asked
     * for after the fact.
     *
     * @param mixed $target
     */
    private function statusOf($target): ?PaymentStatus
    {
        $status = new GetHumanStatus($target);

        try {
            $this->gateway->execute($status);
        } catch (RequestNotSupportedException) {
            // A gateway with no status action says nothing, which is not the same as saying New.
            return null;
        }

        return PaymentStatus::tryFrom((string) $status->getValue());
    }
}
