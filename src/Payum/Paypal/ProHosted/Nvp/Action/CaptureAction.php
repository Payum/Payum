<?php
namespace Payum\Paypal\ProHosted\Nvp\Action;

use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\MergeQuery;
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
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $response = ArrayObject::ensureArrayObject($httpRequest->request);

        if (isset($httpRequest->query['cancelled'])) {
            $newResponse['CANCELLED'] = true;
            $model->replace($newResponse);

            return;
        }

        if ($response['txn_id'] != null) {
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
                $modifier  = new MergeQuery('cancelled=1');
                $cancelUri = $modifier($cancelUri);

                $model['cancel_return'] = (string) $cancelUri;
            }

            $this->gateway->execute(new CreateButtonPayment($model));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Capture && $request->getModel() instanceof \ArrayAccess;
    }
}
