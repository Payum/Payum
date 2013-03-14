<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Action\ActionApiAwareInterface;
use Payum\Action\ActionPaymentAware;
use Payum\Exception\Http\HttpException;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\UnsupportedApiException;
use Payum\PaymentInstructionAggregateInterface;
use Payum\PaymentInstructionAwareInterface;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz\Request;
use Payum\Paypal\ProCheckout\Nvp\PaymentInstruction;
use Payum\Request\CaptureRequest;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class CaptureAction extends ActionPaymentAware implements ActionApiAwareInterface
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

    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        if (false == $request->getModel() instanceof PaymentInstruction) {
            throw new LogicException('Instruction must be initialised and put in to the model');
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

    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof PaymentInstruction
        ;
    }
}
