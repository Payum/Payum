<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway as Executor;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Model\PaymentInterface;
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
        private readonly ?PaymentInterface $payment = null,
        private readonly ?TokenInterface $token = null,
        private readonly array $previous = [],
    ) {
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

    public function payment(): ?PaymentInterface
    {
        return $this->payment;
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
        if (null === $this->payment) {
            throw new LogicException('There is no payment in this context, so there is no state to read.');
        }

        // Cached, so every call within one execution mutates the same instance. Core writes it back onto
        // the payment after the handler returns -- inline for now, PersistStateMiddleware later.
        return $this->state ??= ArrayObject::ensureArrayObject($this->payment->getDetails());
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
}
