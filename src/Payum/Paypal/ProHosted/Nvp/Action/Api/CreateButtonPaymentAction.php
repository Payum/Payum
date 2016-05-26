<?php
namespace Payum\Paypal\ProHosted\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;
use Payum\Core\Exception\RequestNotSupportedException;

class CreateButtonPaymentAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateButtonPayment */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty([
            'subtotal',
            'currency_code',
        ]);

        $result = $this->api->doCreateButton((array) $model);
        $model->replace((array) $result);

        if ($model['EMAILLINK'] != null) {
            throw new HttpRedirect($model['EMAILLINK']);
        } else {
            throw new LogicException('Paypal :'.$model['L_SEVERITYCODE0'].' Code '.$model['L_ERRORCODE0'].': '.$model['L_SHORTMESSAGE0'].'. '.$model['L_LONGMESSAGE0']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof CreateButtonPayment && $request->getModel() instanceof \ArrayAccess;
    }
}
