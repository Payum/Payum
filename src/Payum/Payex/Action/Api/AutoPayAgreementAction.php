<?php

namespace Payum\Payex\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\AutoPayAgreement;

class AutoPayAgreementAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = AgreementApi::class;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request AutoPayAgreement */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validatedKeysSet(array(
            'agreementRef',
            'price',
            'productNumber',
            'description',
            'orderId',
            'purchaseOperation',
            'currency',
        ));

        $result = $this->api->autoPay((array) $model);

        $model->replace($result);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof AutoPayAgreement &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
