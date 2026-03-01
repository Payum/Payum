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
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\PayTheFly\Api;
use Payum\PayTheFly\Constants;
use Payum\PayTheFly\Exception\InvalidSignatureException;

/**
 * Handles PayTheFly webhook notifications.
 *
 * Verifies the HMAC-SHA256 signature and updates the payment model
 * with transaction details from the webhook.
 */
class NotifyAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        /** @var Api $api */
        $api = $this->api;

        try {
            $data = $api->parseWebhook($httpRequest->content);
        } catch (InvalidSignatureException $e) {
            throw new HttpResponse('invalid signature', 400);
        }

        // Update model with webhook data
        $model['webhook_data'] = $data;
        $model['tx_hash'] = $data['tx_hash'] ?? null;
        $model['wallet'] = $data['wallet'] ?? null;
        $model['value'] = $data['value'] ?? null;
        $model['fee'] = $data['fee'] ?? null;
        $model['chain_symbol'] = $data['chain_symbol'] ?? null;
        $model['tx_type'] = $data['tx_type'] ?? null;
        $model['confirmed'] = $data['confirmed'] ?? false;

        // Map serial_no from webhook to our model
        if (isset($data['serial_no'])) {
            $model['serial_no'] = $data['serial_no'];
        }

        // Update status based on confirmation
        if ($data['confirmed'] ?? false) {
            $model['status'] = Constants::STATUS_CONFIRMED;
        } elseif (! $model['status'] || $model['status'] === Constants::STATUS_NEW) {
            $model['status'] = Constants::STATUS_PENDING;
        }

        // Response MUST contain "success" string
        throw new HttpResponse('success', 200);
    }

    public function supports($request): bool
    {
        return $request instanceof Notify
            && $request->getModel() instanceof ArrayAccess;
    }
}
