<?php
namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\PaymentConfigInterface;
use Payum\Core\PaymentFactoryInterface;
use Payum\Core\Storage\StorageInterface;

class DynamicPaymentRegistry implements PaymentRegistryInterface
{
    /**
     * @var StorageInterface
     */
    private $configStorage;

    /**
     * @var PaymentFactoryInterface[]
     */
    private $factories;

    /**
     * @param StorageInterface $configStorage
     * @param PaymentFactoryInterface[] $factories
     */
    public function __construct(StorageInterface $configStorage, array $factories)
    {
        $this->configStorage = $configStorage;
        $this->factories = $factories;
    }

    /**
     * @param string|null $name
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if payment with such name not exist
     *
     * @return \Payum\Core\PaymentInterface
     */
    public function getPayment($name = null)
    {
        /** @var PaymentConfigInterface $paymentConfig */
        $paymentConfig = $this->configStorage->find($name);
        if (false == $paymentConfig) {
            throw new InvalidArgumentException(sprintf('Payum payment named %s does not exist.', $name));
        }

        if (false == $paymentConfig instanceof PaymentConfigInterface) {
            throw new \LogicException(sprintf('Storage must return instance of PaymentConfigInterface but it is %s', get_class($paymentConfig)));
        }

        if (false == $paymentFactory = $this->findPaymentFactory($paymentConfig->getFactoryName())) {
            throw new \LogicException(sprintf('Could not find factory with name %s', $paymentConfig->getFactoryName()));
        }

        return $paymentFactory->create($paymentConfig->getConfig());
    }

    /**
     * @return \Payum\Core\PaymentInterface[]
     */
    public function getPayments()
    {
        throw new LogicException('Method is not supported.');
    }

    /**
     * @param string $name
     *
     * @return PaymentFactoryInterface|null
     */
    protected function findPaymentFactory($name)
    {
        foreach ($this->factories as $factory) {
            $config = $factory->createConfig();
            if (isset($config['factory.name']) && $name === $config['factory.name']) {
                return $factory;
            }
        }
    }
}
