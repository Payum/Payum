<?php
namespace Payum\Skeleton\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;

class NotifyAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // This action is called when calling: $gateway->execute(new Notify($token))
        // See: https://github.com/Payum/Payum/blob/master/docs/examples/notify-script.md
        //
        // If you created a notification token and you sent the notification URL to the bank,
        // the bank should call this URL at different steps of the payment (e.g.: when payment is successful).
        //
        // The simplest logic to implement here is to take GET or POST parameters and use them to replace your model:

        /*
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        $model->replace($httpRequest->request); // takes POST parameters
        $model->replace($httpRequest->request); // or taks GET parameters

        throw new HttpResponse('OK', 200);
        */

        throw new \LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
