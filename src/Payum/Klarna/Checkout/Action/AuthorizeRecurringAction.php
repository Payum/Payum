<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;

/**
 * @param Config $api
 * @param Config $config
 */
class AuthorizeRecurringAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait {
        setApi as _setApi;
    }
    use GatewayAwareTrait;

    /**
     * @deprecated BC. will be removed in 2.x. Use $this->api
     *
     * @var Config
     */
    protected $config;

    public function __construct()
    {
        $this->apiClass = Config::class;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        $this->_setApi($api);

        // BC. will be removed in 2.x. Use $this->api
        $this->config = $this->api;
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

        if ($model['reservation']) {
            return;
        }

        $model['activate'] = false;

        $backupConfig = clone $this->api;

        $token = $model['recurring_token'];

        try {
            unset($model['recurring_token']);

            $baseUri = Constants::BASE_URI_LIVE == $backupConfig->baseUri ?
                Constants::BASE_URI_RECURRING_LIVE :
                Constants::BASE_URI_RECURRING_SANDBOX
            ;

            $this->api->contentType = Constants::CONTENT_TYPE_RECURRING_ORDER_V1;
            $this->api->acceptHeader = Constants::ACCEPT_HEADER_RECURRING_ORDER_ACCEPTED_V1;
            $this->api->baseUri = str_replace('{recurring_token}', $token, $baseUri);

            $this->gateway->execute($createOrderRequest = new CreateOrder($model));

            $model->replace($createOrderRequest->getOrder()->marshal());
        } catch (\Exception $e) {
            $this->api->contentType = $backupConfig->contentType;
            $this->api->acceptHeader = $backupConfig->acceptHeader;
            $this->api->baseUri = $backupConfig->baseUri;

            $model['recurring_token'] = $token;

            throw $e;
        }

        $model['recurring_token'] = $token;

        $this->api->contentType = $backupConfig->contentType;
        $this->api->acceptHeader = $backupConfig->acceptHeader;
        $this->api->baseUri = $backupConfig->baseUri;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == ($request instanceof Authorize && $request->getModel() instanceof \ArrayAccess)) {
            return false;
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        return false == $model['recurring'] && $model['recurring_token'];
    }
}
