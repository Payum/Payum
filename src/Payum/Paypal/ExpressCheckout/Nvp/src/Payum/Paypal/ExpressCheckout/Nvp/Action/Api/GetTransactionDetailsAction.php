<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Buzz\Message\Form\FormRequest;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetailsRequest;
use Payum\Exception\RequestNotSupportedException;

class GetTransactionDetailsAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request GetTransactionDetailsRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $transactionIndex = 'PAYMENTREQUEST_'.$request->getPaymentRequestN().'_TRANSACTIONID';
        if (false == $model[$transactionIndex]) {
            throw new LogicException($transactionIndex.' must be set.');
        }

        $buzzRequest = new FormRequest();
        $buzzRequest->setField('TRANSACTIONID', $model[$transactionIndex]);
        
        $response = $this->api->getTransactionDetails($buzzRequest);
        foreach ($response as $name => $value) {
            if (in_array($name, $this->getPaymentRequestNFields())) {
                $model['PAYMENTREQUEST_'.$request->getPaymentRequestN().'_'.$name] = $value;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof GetTransactionDetailsRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    /**
     * @return array
     */
    protected function getPaymentRequestNFields()
    {
        return array(
            'TRANSACTIONID',
            'PARENTTRANSACTIONID',
            'RECEIPTID',
            'TRANSACTIONTYPE',
            'PAYMENTTYPE',
            'ORDERTIME',
            'AMT',
            'CURRENCYCODE',
            'FEEAMT',
            'SETTLEAMT',
            'TAXAMT',
            'EXCHANGERATE',
            'PAYMENTSTATUS',
            'PENDINGREASON',
            'REASONCODE'
        );
    }
}