<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Capture;
use Payum\Stripe\Request\Api\CreateCharge;
use Payum\Stripe\Request\Api\CreateCustomer;
use Payum\Stripe\Request\Api\ObtainToken;

class CreateCustomerExtension implements ExtensionInterface
{
    /**
     * @var Context $context
     */
    public function onPreExecute(Context $context)
    {
        /** @var Capture $request */
        $request = $context->getRequest();
        if (false == $request instanceof Capture) {
            return;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return;
        }
        $model = ArrayObject::ensureArrayObject($model);

        if (false == $model['save_card']) {
            return;
        }
        if (false == ($model['card'] && is_string($model['card']))) {
            return;
        }

        $customer = ArrayObject::ensureArrayObject($model->get('customer', []));
        $customer['card'] = $model['card'];
        $context->getGateway()->execute(new CreateCustomer($customer));

        $model['customer'] = $customer->toUnsafeArray();
        unset($model['card']);
    }

    /**
     * @var Context $context
     */
    public function onExecute(Context $context)
    {
    }

    /**
     * @var Context $context
     */
    public function onPostExecute(Context $context)
    {
    }
}
