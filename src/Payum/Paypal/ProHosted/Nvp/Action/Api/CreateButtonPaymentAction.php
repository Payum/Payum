<?php
namespace Payum\Paypal\ProHosted\Nvp\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Paypal\ProHosted\Nvp\Api;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;

/**
 * @property Api $api
 */
class CreateButtonPaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

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
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateButtonPayment &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
