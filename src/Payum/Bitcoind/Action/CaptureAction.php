<?php
namespace Payum\Bitcoind\Action;

use Payum\Bitcoind\Request\Api\GetNewAddress;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Request\Sync;

class CaptureAction extends PaymentAwareAction
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $details['address']) {
            $this->payment->execute(new GetNewAddress($details));
        }

        $this->payment->execute(new Sync($details));

        if (false == $details['received_amount']) {
            $this->payment->execute($renderTemplate = new RenderTemplate($this->templateName, array(
                'address' => $details['address'],
                'uri' => $details['address'] . '?' . http_build_query(array_filter(array(
                    'amount' => $details['amount'],
                    'label' => $details['label'],
                    'message' => $details['message'],
//                    'r' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
                )))
            )));

            throw new HttpResponse($renderTemplate->getResult());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}