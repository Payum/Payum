<?php
namespace Payum\Stripe\Js\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\Http\GetRequestRequest;
use Payum\Core\Request\RenderTemplateRequest;
use Payum\Core\Request\Http\ResponseInteractiveRequest;

class CaptureAction extends PaymentAwareAction
{
    /**
     * @var string
     */
    private $templateName;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $getHttpRequest = new GetRequestRequest;
        $this->payment->execute($getHttpRequest);

        if ($getHttpRequest->method == 'POST') {
            if (false == isset($getHttpRequest->request['stripeToken'])) {
                throw new LogicException('The stripe token has to be set');
            }

            try {
                $charge = \Stripe_Charge::create(array(
                    "amount" => 1000, // amount in cents, again
                    "currency" => "usd",
                    "card" => $getHttpRequest->request['stripeToken'],
                    "description" => "payinguser@example.com"
                ));
            } catch(\Stripe_CardError $e) {
                // TOOD update model
            }
        } else {
            $renderTemplate = new RenderTemplateRequest($this->templateName, array());
            $this->payment->execute($renderTemplate);

            throw new ResponseInteractiveRequest($renderTemplate->getResult());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}