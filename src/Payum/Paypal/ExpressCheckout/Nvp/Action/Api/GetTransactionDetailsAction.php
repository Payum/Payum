<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails;

class GetTransactionDetailsAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    public function execute(mixed $request): void
    {
        /** @var GetTransactionDetails $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $transactionIndex = 'PAYMENTREQUEST_' . $request->getPaymentRequestN() . '_TRANSACTIONID';
        if (false == $model[$transactionIndex]) {
            throw new LogicException($transactionIndex . ' must be set.');
        }

        $result = $this->api->getTransactionDetails([
            'TRANSACTIONID' => $model[$transactionIndex],
        ]);
        foreach ($result as $name => $value) {
            if (in_array($name, $this->getPaymentRequestNFields())) {
                $model['PAYMENTREQUEST_' . $request->getPaymentRequestN() . '_' . $name] = $value;
            }
        }
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof GetTransactionDetails &&
            $request->getModel() instanceof ArrayAccess
        ;
    }

    protected function getPaymentRequestNFields(): array
    {
        return [
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
            'REASONCODE',
        ];
    }
}
