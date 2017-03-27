<?php
namespace Payum\Payex\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Payex\Api\AgreementApi;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Request\Api\CreateAgreement;

class CreateAgreementAction implements ActionInterface, ApiAwareInterface
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
        /** @var $request CreateAgreement */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['agreementRef']) {
            throw new LogicException('The agreement has already been created.');
        }

        $model->validatedKeysSet(array(
            'merchantRef',
            'description',
            'purchaseOperation',
            'maxAmount',
            'startDate',
            'stopDate',
        ));

        $model->validateNotEmpty(array(
            'maxAmount',
            'merchantRef',
            'description',
        ));

        $result = $this->api->create((array) $model);

        $model->replace($result);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateAgreement &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
