<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest;
use Payum\Exception\RequestNotSupportedException;

class GetTransactionDetailsAction extends ActionPaymentAware
{
    public function execute($request)
    {
        /** @var $request GetTransactionDetailsRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $instruction = $request->getInstruction();
        
        $transactionId = $instruction->getPaymentrequestTransactionid($request->getPaymentRequestN());
        if (false == $transactionId) {
            throw new LogicException('The TransactionId must be set.');
        }

        $buzzRequest = new FormRequest();
        $buzzRequest->setField('TRANSACTIONID', $transactionId);
        
        $response = $this->payment->getApi()->getTransactionDetails($buzzRequest);
        
        $paymentRequestFields = array(
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
        
        $prefixPaymentRequest = 'PAYMENTREQUEST_'.$request->getPaymentRequestN().'_';
        $nvp = iterator_to_array($response);
        foreach ($nvp as $name => $value) {
            if (in_array($name, $paymentRequestFields)) {
                unset($nvp[$name]);
                
                $nvp[$prefixPaymentRequest.$name] = $value;
            }
        }
        
        $instruction->fromNvp($nvp);
    }

    public function supports($request)
    {
        return $request instanceof GetTransactionDetailsRequest;
    }
}