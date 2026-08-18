<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\PayoutCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Payum\Core\Result\Result;
use Payum\Core\Security\TokenInterface;

/**
 * Turns a 1.x request into the command that means the same thing.
 *
 * What lets an application keep calling execute(new Capture($token)) against a gateway that has been
 * ported to handlers. Gateway packages release on their own schedule, so an application should not break
 * because one of its dependencies moved on.
 *
 * @deprecated since 2.0, removed in 3.0 along with the requests it translates.
 */
final class RequestToCommand
{
    /**
     * Whether a request has a command that means the same thing, without looking anything up.
     *
     * Worth asking before {@see self::translate()}, which resolves the subject a request points at and so
     * may go to storage to do it.
     */
    public static function supports(object $request): bool
    {
        return $request instanceof Capture
            || $request instanceof Authorize
            || $request instanceof Refund
            || $request instanceof Cancel
            || $request instanceof Sync
            || $request instanceof Payout
            || $request instanceof Notify
        ;
    }

    /**
     * Null when nothing means the same thing, or when there is nothing usable to point a command at. The
     * caller then behaves as it did before, which is to say it reports the request as unsupported.
     *
     * @param SubjectInterface|null $subject what the request resolves to, when the caller has already
     *                                       looked it up from a token
     *
     * @return CommandInterface<Result>|null
     */
    public static function translate(object $request, ?SubjectInterface $subject = null): ?CommandInterface
    {
        if (! self::supports($request)) {
            return null;
        }

        /** @var Generic $request */
        $token = $request->getToken();
        $subject ??= $request->getFirstModel() instanceof SubjectInterface ? $request->getFirstModel() : null;

        $payment = $subject instanceof PaymentInterface ? $subject : null;
        $payout = $subject instanceof PayoutInterface ? $subject : null;

        return match (true) {
            $request instanceof Capture => match (true) {
                $token instanceof TokenInterface => CaptureCommand::forToken($token),
                $payment instanceof PaymentInterface => CaptureCommand::forPayment($payment),
                default => null,
            },
            $request instanceof Authorize => match (true) {
                $token instanceof TokenInterface => AuthorizeCommand::forToken($token),
                $payment instanceof PaymentInterface => AuthorizeCommand::forPayment($payment),
                default => null,
            },
            $request instanceof Refund => match (true) {
                $token instanceof TokenInterface => RefundCommand::forToken($token),
                $payment instanceof PaymentInterface => RefundCommand::forPayment($payment),
                default => null,
            },
            // Cancel and Sync take either, because cancelling and refreshing mean the same thing
            // whatever they are pointed at.
            $request instanceof Cancel => match (true) {
                $token instanceof TokenInterface => CancelCommand::forToken($token),
                $payout instanceof PayoutInterface => CancelCommand::forPayout($payout),
                $payment instanceof PaymentInterface => CancelCommand::forPayment($payment),
                default => null,
            },
            $request instanceof Sync => match (true) {
                $token instanceof TokenInterface => SyncCommand::forToken($token),
                $payout instanceof PayoutInterface => SyncCommand::forPayout($payout),
                $payment instanceof PaymentInterface => SyncCommand::forPayment($payment),
                default => null,
            },
            // A notify may point at nothing at all, but a request that resolved to neither a token nor a
            // payment is one of the 1.x actions that match on a details array. Leave it to them.
            $request instanceof Notify => match (true) {
                $token instanceof TokenInterface => NotifyCommand::forToken($token),
                $payment instanceof PaymentInterface => NotifyCommand::forPayment($payment),
                default => null,
            },
            $request instanceof Payout => match (true) {
                $token instanceof TokenInterface => PayoutCommand::forToken($token),
                $payout instanceof PayoutInterface => PayoutCommand::forPayout($payout),
                default => null,
            },
            default => null,
        };
    }
}
