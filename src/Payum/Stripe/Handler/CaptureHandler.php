<?php

declare(strict_types=1);

namespace Payum\Stripe\Handler;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Stripe\Api\StripeCheckoutApi;

/**
 * STUB -- illustrates a two-phase redirect capture. The Api calls it makes are not implemented.
 *
 * Compare with Payum\Stripe\Action\CaptureAction, which this would eventually replace: no supports(), no
 * gateway sub-requests for ObtainToken and CreateCharge, no ArrayObject::ensureArrayObject on the request
 * model, and an answer that comes back as a return value instead of an exception.
 *
 * The constructor takes only the Api. The config is deliberately *not* injected here -- the Api already
 * holds it, and a handler that needs a credential is usually a sign the Api is missing a method.
 */
final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly StripeCheckoutApi $api
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $state = $context->state();

        // ── Phase 2 ────────────────────────────────────────────────────────────────────────────────
        // Reached because the customer finished on Stripe's hosted page and Stripe sent them back to
        // $token->getTargetUrl() -- the very URL that dispatched phase 1. Core dispatched the identical
        // CaptureCommand again; the only thing that changed is that a session id is now in state.
        if ($state['session_id']) {
            $session = $this->api->retrieveSession($state['session_id']);
            $state['payment_status'] = $session['payment_status'] ?? null;

            return match ($session['payment_status'] ?? null) {
                'paid' => CaptureResult::captured(
                    transactionId: $session['payment_intent'] ?? null,
                    capturedAmount: $session['amount_total'] ?? null,
                    raw: $session,
                ),
                // The customer is still on Stripe, or the session expired unused.
                'unpaid' => CaptureResult::failed(
                    new Failure(FailureReason::Declined, $session['status'] ?? null),
                    $session,
                ),
                default => CaptureResult::pending(raw: $session),
            };
        }

        // ── Phase 1 ────────────────────────────────────────────────────────────────────────────────
        // First time through. Open a Checkout Session and send the customer to it.
        //
        // Both URLs point back at this same capture token: that is what makes phase 2 happen. The after
        // URL is where the application goes once capture reports it has nothing left to do.
        $session = $this->api->createSession([
            'success_url' => $context->token()?->getTargetUrl(),
            'cancel_url' => $context->token()?->getTargetUrl() . '?cancelled=1',
            'mode' => 'payment',
            'client_reference_id' => $context->payment()?->getNumber(),
            'amount' => $command->amount ?? $context->payment()?->getTotalAmount(),
            'currency' => $context->payment()?->getCurrencyCode(),
        ]);

        // Written to state, so the next dispatch takes the phase 2 branch above. Core persists this onto
        // the payment after the handler returns.
        $state['session_id'] = $session['id'];

        return CaptureResult::pending(new Redirect($session['url']), $session);
    }
}
