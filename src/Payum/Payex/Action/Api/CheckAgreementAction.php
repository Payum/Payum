<?php

namespace Payum\Payex\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\CheckAgreement;

class CheckAgreementAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = AgreementApi::class;
    }

    public function execute($request)
    {
        /** @var CheckAgreement $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty([
            'agreementRef',
        ]);

        $result = $this->api->check((array) $model);

        $model->replace($result);
    }

    public function supports($request)
    {
        return $request instanceof CheckAgreement &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
