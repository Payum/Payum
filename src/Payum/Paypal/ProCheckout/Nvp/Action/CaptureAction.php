<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Action\ActionInterface;
use Payum\ApiAwareInterface;
use Payum\Exception\Http\HttpException;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\UnsupportedApiException;
use Payum\Exception\LogicException;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz\Request;
use Payum\Paypal\ProCheckout\Nvp\PaymentInstruction;
use Payum\Request\CaptureRequest;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class CaptureAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritdoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /** @var $instruction PaymentInstruction */
        $instruction = $request->getModel();
        $buzzRequest = new Request();
        $buzzRequest->setFields($instruction->toNvp());
        $exception = null;
        try {
            $response = $this->api->doPayment($buzzRequest);
        } catch (HttpException $e) {
            $response = $e->getResponse();
            $exception = $e;
        }

        $instruction->fromNvp($response);

        if ($exception) {
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof PaymentInstruction
        ;
    }
}
