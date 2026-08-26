<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Money\Money;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\HasAmount;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway as Executor;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Money\Amount;
use Payum\Core\Result\Result;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Everything about *this one execution*.
 *
 * The dividing line is lifetime, and holding it is what stops this becoming a service locator:
 *
 *   constructor  -- lives as long as the gateway: api, config, storage, renderer, logger
 *   Context      -- lives for this execution only: payment, token, HTTP request, PSP state
 *
 * The HTTP request is the clearest case. It is request-scoped, so injecting it into a container singleton
 * is where DI setups usually go wrong; passing it here sidesteps that entirely.
 *
 * Not to be confused with {@see \Payum\Core\Extension\Context}, which belongs to the v1 extension
 * mechanism and is typed to actions and replies.
 */
final class Context
{
    /**
     * @var ArrayObject<string, mixed>|null
     */
    private ?ArrayObject $state = null;

    private ?TokenInterface $mintedNotifyToken = null;

    /**
     * @param CommandInterface<Result> $command
     * @param list<self> $previous the enclosing executions, when a handler dispatches a sub-command
     */
    public function __construct(
        private readonly Executor $executor,
        private readonly CommandInterface $command,
        private readonly GatewayInterface $gateway,
        private readonly ServerRequestInterface $httpRequest,
        private readonly GenericTokenFactoryInterface $tokenFactory,
        private readonly ?SubjectInterface $subject = null,
        private readonly ?TokenInterface $token = null,
        private readonly array $previous = [],
        private readonly ?string $gatewayName = null,
    ) {
    }

    /**
     * How much this execution is for: what the command asked for, or the subject's full amount when it
     * asked for nothing.
     *
     * This is the one a handler wants. Reading $command->amount instead gets a partial capture right and
     * a full one wrong, because a full one carries no amount at all.
     *
     * Null when the subject names no currency, which for a command that gave no amount either means there
     * is nothing to work out.
     */
    public function amount(): ?Money
    {
        $subject = Amount::of($this->subject);

        if (! $this->command instanceof HasAmount) {
            return $subject;
        }

        return $this->command->money($subject?->getCurrency()) ?? $subject;
    }

    /**
     * @return CommandInterface<Result>
     */
    public function command(): CommandInterface
    {
        return $this->command;
    }

    /**
     * Dispatches a sub-command on the gateway currently executing.
     *
     * The v1 counterpart is GatewayAwareTrait::$gateway->execute(). Sub-dispatches push onto the same
     * stack, which is what {@see self::previous()} exposes. The executor itself is deliberately not
     * exposed: a handler has no business holding the thing that is presently calling it.
     *
     * Returns Result rather than the command's own result type. Narrowing to CaptureResult and friends
     * needs the PHPStan extension on Gateway::execute(), which lands with the executor.
     *
     * @param CommandInterface<Result> $command
     */
    public function execute(CommandInterface $command): Result
    {
        return $this->executor->execute($command);
    }

    /**
     * The gateway currently executing
     */
    public function gateway(): GatewayInterface
    {
        return $this->gateway;
    }

    /**
     * The inbound request, as PSR-7.
     *
     * Replaces v1's GetHttpRequest sub-request and its $_REQUEST-shaped array. A handler reads query
     * parameters here to tell "the customer just came back from the PSP" from "the customer is starting".
     */
    public function httpRequest(): ServerRequestInterface
    {
        return $this->httpRequest;
    }

    /**
     * The state to write back, or null when the handler never touched it.
     *
     * Deliberately the ArrayObject rather than toUnsafeArray(): a SensitiveValue must stay wrapped on
     * its way into storage.
     *
     * @internal for the executor
     *
     * @return ArrayObject<string, mixed>|null
     */
    public function pendingState(): ?ArrayObject
    {
        return $this->state;
    }

    /**
     * What this command is operating on, whatever it is. Handlers normally want one of the narrower
     * accessors below.
     */
    public function subject(): ?SubjectInterface
    {
        return $this->subject;
    }

    /**
     * Null when the subject is not a payment, which is the case for a payout.
     */
    public function payment(): ?PaymentInterface
    {
        return $this->subject instanceof PaymentInterface ? $this->subject : null;
    }

    /**
     * Null when the subject is not a payout.
     */
    public function payout(): ?PayoutInterface
    {
        return $this->subject instanceof PayoutInterface ? $this->subject : null;
    }

    /**
     * The executions enclosing this one, outermost first. Empty at the top level.
     *
     * This is the *same-request* answer to "what did the previous command return". The cross-request
     * answer is {@see self::state()}: results from an earlier HTTP request are not in memory, and the
     * only thing that survives is what was persisted.
     *
     * @return list<self>
     */
    public function previous(): array
    {
        return $this->previous;
    }

    /**
     * The PSP state carried across requests -- v1's "details" array, unchanged.
     *
     * This is what makes a re-entrant capture work: phase one writes the PSP's token here, core persists
     * it onto the payment, and phase two reads it back on the next HTTP request to know where it left off.
     *
     * Kept as ArrayObject rather than a plain array for four reasons, in order of how much they matter:
     *
     *   1. toUnsafeArray() unwraps SensitiveValue, so card data stays wrapped in storage and logs and is
     *      unwrapped only when it is actually sent to the PSP. A plain array loses that silently.
     *   2. It is an object, so $context->state()['TOKEN'] = 'x' is visible to core with no write-back
     *      call that a handler could forget on one branch.
     *   3. offsetGet() returns null for a missing key instead of warning, so `if (! $state['TOKEN'])`
     *      needs no isset -- which is what lets a v1 action body port across as a copy-paste.
     *   4. defaults(), validateNotEmpty(), getArray() and replace() are already used throughout the
     *      existing actions.
     *
     * Transitional. A later pass adds state(StripeState::class) returning a typed per-gateway object
     * hydrated from these same details, so both shapes coexist and a gateway opts in per handler.
     *
     * @return ArrayObject<string, mixed>
     */
    public function state(): ArrayObject
    {
        if (! $this->subject instanceof SubjectInterface) {
            throw new LogicException('There is nothing in this context to read state from.');
        }

        // Cached, so every call within one execution mutates the same instance. Core writes it back onto
        // the payment after the handler returns -- inline for now, PersistStateMiddleware later.
        return $this->state ??= ArrayObject::ensureArrayObject($this->subject->getDetails());
    }

    /**
     * The capture token, when this execution came in over HTTP.
     *
     * Its target URL is the URL the customer is on right now, which is what a redirect gateway hands the
     * PSP as its return URL -- that is precisely how the same command comes back for its second phase.
     */
    public function token(): ?TokenInterface
    {
        return $this->token;
    }

    /**
     * Mints tokens, for gateways that need a notify URL or a second hop.
     *
     * Replaces v1's GenericTokenFactoryAwareInterface / GenericTokenFactoryExtension pair.
     */
    public function tokens(): GenericTokenFactoryInterface
    {
        return $this->tokenFactory;
    }

    /**
     * The name this gateway is registered under, which every token the factory mints needs.
     *
     * Null for a gateway assembled by hand rather than through PayumBuilder.
     */
    public function gatewayName(): ?string
    {
        return $this->gatewayName;
    }

    /**
     * A long-lived token whose URL the PSP posts to when something happens.
     *
     * Points at this execution's subject, or at the gateway alone when there is none.
     *
     * @throws LogicException when the gateway is registered under no name
     */
    public function notifyToken(): TokenInterface
    {
        if (null === $this->gatewayName) {
            throw new LogicException(
                'This gateway is not registered under a name, so a notify token has nothing to name as its gateway. Register it with PayumBuilder::registerGateway().'
            );
        }

        return $this->mintedNotifyToken ??= $this->tokenFactory->createNotifyToken($this->gatewayName, $this->subject);
    }

    /**
     * The URL to hand a PSP that takes a notification address per payment.
     *
     * Minted once for this execution. Reusing it across requests is the gateway's own business: keep it
     * in {@see self::state()} and mint only when it is absent, or a customer who retries gets a second
     * token row.
     *
     * @throws LogicException when the gateway is registered under no name
     */
    public function notifyUrl(): string
    {
        return $this->notifyToken()->getTargetUrl();
    }
}
