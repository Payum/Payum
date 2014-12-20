<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateBillingAgreement;

class CreateBillingAgreementAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateBillingAgreement */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['TOKEN']) {
            throw new LogicException('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        }

        $model->replace(
            $this->api->createBillingAgreement((array) $model)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateBillingAgreement &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
