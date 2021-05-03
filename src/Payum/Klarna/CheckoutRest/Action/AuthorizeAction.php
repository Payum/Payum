<?php
namespace Payum\Klarna\CheckoutRest\Action;

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
use Payum\Klarna\CheckoutRest\Request\Api\CreateOrder;
use Payum\Klarna\Common\Config;
use Payum\Klarna\Common\Constants;

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

        $merchantUrls = ArrayObject::ensureArrayObject($model['merchant_urls'] ?: []);

        if (false == $merchantUrls['checkout'] && $this->api->checkoutUri) {
            $merchantUrls['checkout'] = $this->api->checkoutUri;
        }

        if (false == $merchantUrls['terms'] && $this->api->termsUri) {
            $merchantUrls['terms'] = $this->api->termsUri;
        }

        if (false == $merchantUrls['confirmation'] && $request->getToken()) {
            $merchantUrls['confirmation'] = $request->getToken()->getTargetUrl();
        }

        if (empty($merchantUrls['push']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );

            $merchantUrls['push'] = $notifyToken->getTargetUrl();
        }

        $merchantUrls->validateNotEmpty(['checkout', 'terms', 'confirmation', 'push']);
        $model['merchant_urls'] = (array) $merchantUrls;

        if (false == $model['location']) {
            $createOrderRequest = new CreateOrder($model);
            $this->gateway->execute($createOrderRequest);

            $model->replace($createOrderRequest->getOrder());
            $model['location'] = $createOrderRequest->getOrder()->getLocation();
        }

        $this->gateway->execute(new Sync($model));

        if (Constants::STATUS_CHECKOUT_INCOMPLETE == $model['status']) {
            $renderTemplate = new RenderTemplate($this->templateName, array(
                'snippet' => $model['html_snippet'],
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
