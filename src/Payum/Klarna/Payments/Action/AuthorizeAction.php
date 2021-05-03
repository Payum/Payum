<?php
namespace Payum\Klarna\Payments\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Klarna\Common\Config;
use Payum\Klarna\Payments\Request\Api\CreateOrder;

/**
 * @property Config $api
 */
class AuthorizeAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct()
    {
        $this->apiClass = Config::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $merchantUrls = ArrayObject::ensureArrayObject($model['merchant_urls'] ?: []);

        if (false == $merchantUrls['confirmation'] && $request->getToken()) {
            $merchantUrls['confirmation'] = $request->getToken()->getTargetUrl();
        }

        if (empty($merchantUrls['notification']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );

            $merchantUrls['notification'] = $notifyToken->getTargetUrl();
        }

        $merchantUrls->validateNotEmpty(['checkout']);
        $model['merchant_urls'] = (array) $merchantUrls;

        if (false == $model['location']) {
            $createOrderRequest = new CreateOrder($model);
            $this->gateway->execute($createOrderRequest);

            $model->replace($createOrderRequest->getOrder());
            $model['location'] = $createOrderRequest->getOrder()->getLocation();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
