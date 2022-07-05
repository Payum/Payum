<?php

namespace Payum\Paypal\ProHosted\Nvp\Action;

use League\Uri\Http as HttpUri;
use League\Uri\UriModifier;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Sync;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        $newResponse = [];
        /** @var Capture $request */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $response = ArrayObject::ensureArrayObject($httpRequest->request);

        if (isset($httpRequest->query['cancelled'])) {
            $newResponse['CANCELLED'] = true;
            $model->replace($newResponse);

            return;
        }

        if (null != $response['txn_id']) {
            $response->validateNotEmpty([
                'payment_status',
                'business',
                'invoice',
                'txn_id',
                'mc_gross',
            ]);

            $this->gateway->execute(new Sync($response));

            $model->replace($response);
        } else {
            if ($model['cancel_return']) {
                $cancelUri = HttpUri::createFromString($model['cancel_return']);
                $cancelUri = UriModifier::mergeQuery($cancelUri, 'cancelled=1');

                $model['cancel_return'] = (string) $cancelUri;
            }

            $this->gateway->execute(new CreateButtonPayment($model));
        }
    }

    public function supports($request)
    {
        return $request instanceof Capture && $request->getModel() instanceof \ArrayAccess;
    }
}
