<?php

namespace Payum\Sofort\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Sofort\Api;
use Payum\Sofort\Request\Api\CreateTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;

class CreateTransactionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param $request CreateTransaction
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['transaction_id']) {
            throw new LogicException(sprintf('The transaction has already been created for this payment. transaction_id: %s', $details['transaction_id']));
        }

        $fieldsRequired = ['amount', 'currency_code', 'reason', 'success_url'];

        if ($this->api->getOption('disable_notification') == false) {
            $fieldsRequired[] = 'notification_url';
        }

        $details->validateNotEmpty($fieldsRequired);

        $details->replace($this->api->createTransaction((array) $details));

        if ($details['payment_url']) {
            throw new HttpRedirect($details['payment_url']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateTransaction &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
