<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CancelRecurringPaymentsProfileAction as NewCancelAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use function trigger_deprecation;

trigger_deprecation('payum/paypal-express-checkout-nvp', '1.4', 'The %s class is deprecated and will be removed in 3.0. Use %s instead.', CancelRecurringPaymentsProfileAction::class, NewCancelAction::class);

/**
 * Class CancelRecurringPaymentsProfileAction.
 *
 * @deprecated since version 1.4, will be removed in 3.0.
 *             Use {@link Payum\Paypal\ExpressCheckout\Nvp\Action\CancelRecurringPaymentsProfileAction} instead.
 */
class CancelRecurringPaymentsProfileAction extends NewCancelAction implements ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }
}
