<?php

namespace Payum\PayTheFly\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\PayTheFly\Api;
use Payum\PayTheFly\Constants;

/**
 * Capture action for PayTheFly.
 *
 * Generates an EIP-712 signed payment URL and redirects the user
 * to the PayTheFly payment page.
 */
class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // If already captured or confirmed, do nothing
        if ($model['status'] && $model['status'] !== Constants::STATUS_NEW) {
            return;
        }

        // Check if we're returning from PayTheFly
        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if ($httpRequest->query) {
            // User returned from PayTheFly — mark as pending
            $model['status'] = Constants::STATUS_PENDING;

            return;
        }

        /** @var Api $api */
        $api = $this->api;

        $amount = $model['amount'] ?? '0';
        $serialNo = $model['serial_no'] ?? $model['number'] ?? '';
        $deadline = $model['deadline'] ?? (time() + 3600); // Default: 1 hour
        $token = $model['token_address'] ?? null;

        // Store metadata for status tracking
        $model['status'] = Constants::STATUS_PENDING;
        $model['chain_id'] = $api->getChainId();
        $model['project_id'] = $api->getProjectId();

        $paymentUrl = $api->buildPaymentUrl(
            (string) $amount,
            (string) $serialNo,
            (int) $deadline,
            $token
        );

        throw new HttpRedirect($paymentUrl);
    }

    public function supports($request): bool
    {
        return $request instanceof Capture
            && $request->getModel() instanceof ArrayAccess;
    }
}
