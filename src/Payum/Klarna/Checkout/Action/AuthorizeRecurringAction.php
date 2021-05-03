<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Authorize;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;

class AuthorizeRecurringAction extends GatewayAwareAction implements ApiAwareInterface
{
    /**
     * @var Config
     */
    protected $config;

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

        if ($model['reservation']) {
            return;
        }

        $model['activate'] = false;

        $backupConfig = clone $this->config;

        $token = $model['recurring_token'];

        try {
            unset($model['recurring_token']);

            $baseUri = Constants::BASE_URI_LIVE == $backupConfig->baseUri ?
                Constants::BASE_URI_RECURRING_LIVE :
                Constants::BASE_URI_RECURRING_SANDBOX
            ;

            $this->config->contentType = Constants::CONTENT_TYPE_RECURRING_ORDER_V1;
            $this->config->acceptHeader = Constants::ACCEPT_HEADER_RECURRING_ORDER_ACCEPTED_V1;
            $this->config->baseUri = str_replace('{recurring_token}', $token, $baseUri);

            $this->gateway->execute($createOrderRequest = new CreateOrder($model));

            $model->replace($createOrderRequest->getOrder()->marshal());
        } catch (\Exception $e) {
            $this->config->contentType = $backupConfig->contentType;
            $this->config->acceptHeader = $backupConfig->acceptHeader;
            $this->config->baseUri = $backupConfig->baseUri;

            $model['recurring_token'] = $token;

            throw $e;
        }

        $model['recurring_token'] = $token;

        $this->config->contentType = $backupConfig->contentType;
        $this->config->acceptHeader = $backupConfig->acceptHeader;
        $this->config->baseUri = $backupConfig->baseUri;
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
