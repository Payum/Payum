<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface as Executor;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Reply\Base;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;

/**
 * Presents a 1.x action as the handler for the command that means the same thing.
 *
 * {@see \Payum\Core\Gateway\DeclaresActions} moves a gateway across in one direction: an action nobody has
 * ported keeps answering the 1.x request it always answered. This moves it in the other: the action stays
 * exactly as it is and answers a command, so an application that has adopted commands reaches it too.
 *
 * Subclass per command -- {@see Handler\CaptureActionHandler} and friends -- because PHP will not let one
 * class declare handle() twice.
 *
 * Three things are worth knowing before reaching for this:
 *
 *   - The action still runs on 1.x machinery, so the gateway has to keep it. Core arranges that: a gateway
 *     whose handler list contains an adapter gets core's own actions and extensions, the same as one
 *     implementing DeclaresActions.
 *   - Status is read the 1.x way, by asking the gateway for {@see GetHumanStatus} once the action has run.
 *     A gateway that has already deleted its status action will report nothing, which is honest rather
 *     than a guess.
 *   - A reply the action throws that no {@see NextAction} means the same as -- a rendered HttpResponse --
 *     is rethrown rather than swallowed.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
abstract class ActionToHandlerAdapter implements HandlerInterface, GatewayAwareInterface
{
    protected ?Executor $gateway = null;

    public function __construct(
        private readonly ActionInterface $action
    ) {
    }

    public function setGateway(Executor $gateway): void
    {
        $this->gateway = $gateway;
    }

    /**
     * Runs the action and reports what it decided, in the two pieces every result is built from.
     *
     * @param class-string<Generic> $requestClass
     *
     * @return array{PaymentStatus|null, NextAction|null}
     *
     * @throws Base when the action threw a reply no next action means the same as
     */
    final protected function run(string $requestClass, Context $context): array
    {
        $reply = $this->dispatch($requestClass, $context);

        if (! $reply instanceof Base) {
            return [$this->status($context), null];
        }

        $next = ReplyToResult::translate($reply);

        if (! $next instanceof NextAction) {
            throw $reply;
        }

        // The customer has somewhere to be, so the operation is not finished. A status action still on the
        // gateway knows better and wins.
        return [$this->status($context) ?? PaymentStatus::Pending, $next];
    }

    /**
     * Runs the action against the 1.x request it expects, returning the reply it threw.
     *
     * @param class-string<Generic> $requestClass
     */
    final protected function dispatch(string $requestClass, Context $context): ?Base
    {
        if ($this->action instanceof GatewayAwareInterface && $this->gateway instanceof Executor) {
            $this->action->setGateway($this->gateway);
        }

        if ($this->action instanceof GenericTokenFactoryAwareInterface) {
            $this->action->setGenericTokenFactory($context->tokens());
        }

        try {
            $this->action->execute($this->request($requestClass, $context));
        } catch (Base $reply) {
            // Only the replies core ships are caught. A reply that implements ReplyInterface without
            // extending Base is not a Throwable as far as the type system is concerned, so it could not
            // be rethrown -- and propagating it untouched is what would happen to it anyway.
            return $reply;
        }

        return null;
    }

    /**
     * What the gateway's status action makes of the state the action left behind.
     *
     * Null when nothing answered, which is what a gateway that has finished porting its status action
     * looks like. Unknown collapses to null too: a result whose status is null concluded nothing, and
     * that is the same statement.
     */
    final protected function status(Context $context): ?PaymentStatus
    {
        if (! $this->gateway instanceof Executor) {
            return null;
        }

        $this->gateway->execute($status = new GetHumanStatus($this->state($context)));

        $value = PaymentStatus::tryFrom((string) $status->getValue());

        return PaymentStatus::Unknown === $value ? null : $value;
    }

    /**
     * The 1.x request the action is waiting for: the details array it reads and writes, plus the token it
     * came in on, which is what a redirect action hands the PSP as its return URL.
     *
     * @param class-string<Generic> $requestClass
     */
    private function request(string $requestClass, Context $context): Generic
    {
        $state = $this->state($context);

        // Constructing with the token is the only way to set one -- Generic has no setter -- and setModel()
        // then puts the details in front of it, which is what the action matches on.
        $request = new $requestClass($context->token() ?? $state);
        $request->setModel($state);

        return $request;
    }

    /**
     * @return ArrayObject<string, mixed>
     */
    private function state(Context $context): ArrayObject
    {
        // A notify may point at nothing at all, and state() has nothing to read from in that case.
        return $context->subject() instanceof SubjectInterface ? $context->state() : new ArrayObject();
    }
}
