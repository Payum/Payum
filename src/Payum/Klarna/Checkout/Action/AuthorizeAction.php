<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;

/**
 * @property Config $api
 */
class AuthorizeAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;
    
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param string|null $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
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

        $merchant = ArrayObject::ensureArrayObject($model['merchant'] ?: []);

        if (false == $merchant['checkout_uri'] && $this->api->checkoutUri) {
            $merchant['checkout_uri'] = $this->api->checkoutUri;
        }

        if (false == $merchant['terms_uri'] && $this->api->termsUri) {
            $merchant['terms_uri'] = $this->api->termsUri;
        }

        if (false == $merchant['confirmation_uri'] && $request->getToken()) {
            $merchant['confirmation_uri'] = $request->getToken()->getTargetUrl();
        }

        if (empty($merchant['push_uri']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );

            $merchant['push_uri'] = $notifyToken->getTargetUrl();
        }

        $merchant->validateNotEmpty(['checkout_uri', 'terms_uri', 'confirmation_uri', 'push_uri']);
        $model['merchant'] = (array) $merchant;

        if (false == $model['location']) {
            $createOrderRequest = new CreateOrder($model);
            $this->gateway->execute($createOrderRequest);

            $model->replace($createOrderRequest->getOrder()->marshal());
            $model['location'] = $createOrderRequest->getOrder()->getLocation();
        }

        $this->gateway->execute(new Sync($model));

        if (Constants::STATUS_CHECKOUT_INCOMPLETE == $model['status']) {
            $renderTemplate = new RenderTemplate($this->templateName, array(
                'snippet' => $model['gui']['snippet'],
            ));
            $this->gateway->execute($renderTemplate);

            throw new HttpResponse($renderTemplate->getResult());
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
