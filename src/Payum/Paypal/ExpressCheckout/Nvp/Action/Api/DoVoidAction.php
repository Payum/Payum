<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails;

class DoVoidAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * {@inheritdoc}
     */
    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request DoCapture */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $paymentRequestN = $request->getPaymentRequestN();

        $fields = new ArrayObject([]);
        $parentTransactionId = $model['PAYMENTREQUEST_'.$paymentRequestN.'_PARENTTRANSACTIONID'];
        $fields['AUTHORIZATIONID'] = $parentTransactionId
            ? $parentTransactionId
            : $model['PAYMENTREQUEST_'.$paymentRequestN.'_TRANSACTIONID']
        ;

        $fields->validateNotEmpty(['AUTHORIZATIONID']);

        $this->api->doVoid((array) $fields);

        $this->gateway->execute(new GetTransactionDetails($model, $paymentRequestN));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof DoVoid &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
