<?php
namespace Payum\Stripe\Extension;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Capture;
use Payum\Stripe\Constants;
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

        if (false == ($model['card'] && is_string($model['card']))) {
            return;
        }

        $local = $model->getArray('local');
        if (false == $local['save_card']) {
            return;
        }

        $customer = $local->getArray('customer');
        $customer['card'] = $model['card'];
        $context->getGateway()->execute(new CreateCustomer($customer));

        $local['customer'] = $customer->toUnsafeArray();
        $model['local'] = $local->toUnsafeArray();
        unset($model['card']);

        if ($customer['id']) {
            $model['customer'] = $customer['id'];
        } else {
            $model['status'] = Constants::STATUS_FAILED;
        }
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
