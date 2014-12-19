<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\FillOrderDetailsAction;
use Payum\Be2Bill\Action\CaptureAction;
use Payum\Be2Bill\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Core\PaymentFactoryInterface;

class DirectPaymentFactory extends CorePaymentFactory
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $corePaymentFactory;

    /**
     * @param PaymentFactoryInterface $corePaymentFactory
     */
    public function __construct(PaymentFactoryInterface $corePaymentFactory = null)
    {
        $this->corePaymentFactory = $corePaymentFactory ?: new CorePaymentFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->validateNotEmpty(array('identifier', 'password'));

        $config->defaults(array(
            'sandbox' => true,

            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.api'] = new Api(array(
                'identifier' => $config['identifier'],
                'password' => $config['password'],
                'sandbox' => $config['sandbox'],
            ));
        }


        return $this->corePaymentFactory->create((array) $config);
    }
}