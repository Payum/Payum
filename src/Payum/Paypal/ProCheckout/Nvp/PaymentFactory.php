<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Core\PaymentFactoryInterface;
use Payum\Paypal\ProCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ProCheckout\Nvp\Action\RefundAction;
use Payum\Paypal\ProCheckout\Nvp\Action\FillOrderDetailsAction;
use Payum\Paypal\ProCheckout\Nvp\Action\StatusAction;

class PaymentFactory implements PaymentFactoryInterface
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
        return $this->corePaymentFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->corePaymentFactory->createConfig());

        $config->defaults(array(
            'payum.action.capture' => new CaptureAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            'payum.action.status' => new StatusAction(),
        ));

        if (false == $config['payum.api']) {
            $config['options.required'] = array('username', 'password', 'partner', 'vendor', 'tender');

            $config->defaults(array(
                'sandbox' => true,
            ));

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

                $paypalConfig = array(
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'partner' => $config['partner'],
                    'vendor' => $config['vendor'],
                    'tender' => $config['tender'],
                    'sandbox' => $config['sandbox'],
                );

                $config['payum.api'] = new Api($paypalConfig, $config['buzz.client']);
            };
        }

        return (array) $config;
    }
}
