<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
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
        if (! $request instanceof Generic) {
            return null;
        }

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
            $request instanceof Payout => match (true) {
                $token instanceof TokenInterface => PayoutCommand::forToken($token),
                $payout instanceof PayoutInterface => PayoutCommand::forPayout($payout),
                default => null,
            },
            default => null,
        };
    }
}
