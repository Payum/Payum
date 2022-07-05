<?php

namespace Payum\Payex\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\DeleteAgreement;

class DeleteAgreementAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = AgreementApi::class;
    }

    public function execute($request)
    {
        /** @var DeleteAgreement $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty([
            'agreementRef',
        ]);

        $result = $this->api->delete((array) $model);

        $model->replace($result);
    }

    public function supports($request)
    {
        return $request instanceof DeleteAgreement &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
