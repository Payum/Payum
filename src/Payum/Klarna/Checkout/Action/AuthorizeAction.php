<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;

class AuthorizeAction extends GatewayAwareAction implements GenericTokenFactoryAwareInterface, ApiAwareInterface
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param string|null $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @param GenericTokenFactoryInterface $genericTokenFactory
     *
     * @return void
     */
    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null)
    {
        $this->tokenFactory = $genericTokenFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Config) {
            throw new UnsupportedApiException('Not supported. Expected Payum\Klarna\Checkout\Config instance to be set as api.');
        }

        $this->config = $api;
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

        if (false == $merchant['checkout_uri'] && $this->config->checkoutUri) {
            $merchant['checkout_uri'] = $this->config->checkoutUri;
        }

        if (false == $merchant['terms_uri'] && $this->config->termsUri) {
            $merchant['terms_uri'] = $this->config->termsUri;
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
