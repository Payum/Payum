<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;

class DoVoidAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param $request DoVoid
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['AUTHORIZATIONID']) {
            throw new LogicException('AUTHORIZATIONID must be set. Has user not authorized this transaction?');
        }

        $model->replace(
            $this->api->doVoid((array) $model)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof DoVoid &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
