<?php

declare(strict_types=1);

namespace Payum\Paypal\ExpressCheckout\Nvp\Handler;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

/**
 * STUB -- the canonical two-phase redirect capture, ported from PurchaseAction + CaptureAction.
 *
 * This is the class to read if you want to understand why capture is re-entrant rather than split into
 * two commands. Every branch below is the same branch v1 takes; the difference is that the phase is read
 * from explicit accessors and the answer is returned instead of thrown.
 *
 * What the v1 version needed and this one does not:
 *
 *   supports()                              -- dispatch is by type now
 *   GatewayAwareTrait                       -- no sub-requests
 *   GenericTokenFactoryAwareTrait           -- $context->tokens()
 *   execute(new GetHttpRequest())           -- $context->httpRequest()
 *   execute(new SetExpressCheckout(...))    -- $this->api->setExpressCheckout(...)
 *   execute(new Sync(...))                  -- $this->api->getExpressCheckoutDetails(...)
 *   throw new HttpRedirect(...)             -- return CaptureResult::pending(new Redirect(...))
 *
 * Six indirections removed, and the whole flow is readable top to bottom in one file.
 */
final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly Api $api
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $state = $context->state();

        // The customer bailed out on Paypal's side and came back via CANCELURL.
        if (isset($context->httpRequest()->getQueryParams()['cancelled'])) {
            $state['CANCELLED'] = true;

            return CaptureResult::canceled($state->toUnsafeArray());
        }

        $state->defaults([
            'PAYMENTREQUEST_0_PAYMENTACTION' => Api::PAYMENTACTION_SALE,
            'AUTHORIZE_TOKEN_USERACTION' => Api::USERACTION_COMMIT,
        ]);

        if (! $state['TOKEN']) {
            return $this->initiate($context, $state);
        }

        return $this->finalise($state);
    }

    /**
     * Phase 1. No Paypal token yet, so this is the customer's first arrival.
     *
     * @param \Payum\Core\Bridge\Spl\ArrayObject<string, mixed> $state
     */
    private function initiate(Context $context, $state): CaptureResult
    {
        $token = $context->token();

        // Both URLs point back at the capture token we are being executed under. That is the entire
        // mechanism behind the second phase: Paypal returns the customer to this same URL, core verifies
        // the token again, and dispatches an identical CaptureCommand.
        $state['RETURNURL'] ??= $token?->getTargetUrl();
        $state['CANCELURL'] ??= $token?->getTargetUrl() . '?cancelled=1';

        if (null === $state['PAYMENTREQUEST_0_NOTIFYURL'] && null !== $token) {
            $state['PAYMENTREQUEST_0_NOTIFYURL'] = $context->tokens()
                ->createNotifyToken($token->getGatewayName(), $token->getDetails())
                ->getTargetUrl();
        }

        $state->replace($this->api->setExpressCheckout($state->toUnsafeArray()));

        if ($state['L_ERRORCODE0']) {
            return CaptureResult::failed(
                new Failure(FailureReason::Unknown, (string) $state['L_ERRORCODE0'], $state['L_LONGMESSAGE0']),
                $state->toUnsafeArray(),
            );
        }

        // Not an exception. The operation is simply not finished, and the customer has somewhere to be.
        return CaptureResult::pending(
            new Redirect($this->api->getAuthorizeTokenUrl($state['TOKEN'], [
                'useraction' => $state['AUTHORIZE_TOKEN_USERACTION'],
            ])),
            $state->toUnsafeArray(),
        );
    }

    /**
     * Phase 2. A token is in state, so the customer has been to Paypal and back.
     *
     * @param \Payum\Core\Bridge\Spl\ArrayObject<string, mixed> $state
     */
    private function finalise($state): CaptureResult
    {
        $state->replace($this->api->getExpressCheckoutDetails([
            'TOKEN' => $state['TOKEN'],
        ]));

        // A token without a payer id means the customer never actually approved -- they hit the return
        // URL some other way. Send them back rather than guessing.
        if (! $state['PAYERID']) {
            return CaptureResult::pending(
                new Redirect($this->api->getAuthorizeTokenUrl($state['TOKEN'])),
                $state->toUnsafeArray(),
            );
        }

        if (
            Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED === $state['CHECKOUTSTATUS']
            && $state['PAYMENTREQUEST_0_AMT'] > 0
        ) {
            $state->replace($this->api->doExpressCheckoutPayment($state->toUnsafeArray()));
        }

        return CaptureResult::captured(
            transactionId: $state['PAYMENTINFO_0_TRANSACTIONID'],
            capturedAmount: null === $state['PAYMENTREQUEST_0_AMT'] ? null : (int) round((float) $state['PAYMENTREQUEST_0_AMT'] * 100),
            raw: $state->toUnsafeArray(),
        );
    }
}
