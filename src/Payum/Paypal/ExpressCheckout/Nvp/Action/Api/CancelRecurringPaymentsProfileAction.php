<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CancelRecurringPaymentsProfileAction as NewCancelAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

@trigger_error('The ' . CancelRecurringPaymentsProfileAction::class . ' class is deprecated since version 1.4 and will be removed in 2.0. Use ' . NewCancelAction::class . ' class instead.', E_USER_DEPRECATED);

/**
 * Class CancelRecurringPaymentsProfileAction.
 *
 * @deprecated since version 1.4, to be removed in 2.0.
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
