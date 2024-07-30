<?php

namespace Payum\Stripe\Action\Api;

use Composer\InstalledVersions;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Stripe\Constants;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreatePrice;
use Stripe\Exception;
use Stripe\Price;
use Stripe\Stripe;

class CreatePriceAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait {
        setApi as _setApi;
    }
    use GatewayAwareTrait;

    /**
     * @deprecated BC will be removed in 2.x. Use $this->api
     *
     * @var Keys
     */
    protected $keys;

    public function __construct()
    {
        $this->apiClass = Keys::class;
    }

    public function setApi($api): void
    {
        $this->_setApi($api);

        // BC. will be removed in 2.x
        $this->keys = $this->api;
    }

    public function execute($request): void
    {
        /** @var CreatePrice $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        try {
            Stripe::setApiKey($this->keys->getSecretKey());

            if (class_exists(InstalledVersions::class)) {
                Stripe::setAppInfo(
                    Constants::PAYUM_STRIPE_APP_NAME,
                    InstalledVersions::getVersion('stripe/stripe-php'),
                    Constants::PAYUM_URL
                );
            }

            $price = Price::create($model->toUnsafeArrayWithoutLocal());

            $model->replace($price->toArray());
        } catch (Exception\ApiErrorException $e) {
            $model->replace($e->getJsonBody());
        }
    }

    public function supports($request): bool
    {
        return $request instanceof CreatePrice &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
