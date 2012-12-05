<?php
namespace Payum\PaymentBundle\Context;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

use Payum\Domain\ModelInterface;

class LazyContext extends ContainerAware implements ContextInterface
{
    /**
     * @var string
     */
    protected $contextName;

    /**
     * @var string
     */
    protected $paymentServiceId;

    /**
     * @var string
     */
    protected $storageServiceId;

    /**
     * @var string
     */
    protected $statusRequestClass;

    /**
     * @var string
     */
    protected $interactiveController;

    /**
     * @var string
     */
    protected $statusController;
    
    /**
     * @param string $contextName
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(
        $contextName, 
        $paymentServiceId, 
        $storageServiceId, 
        $statusRequestClass,
        $interactiveController,
        $statusController
    ) {
        $this->contextName = $contextName;
        $this->paymentServiceId = $paymentServiceId;
        $this->storageServiceId = $storageServiceId;
        $this->statusRequestClass = $statusRequestClass;
        $this->interactiveController = $interactiveController;
        $this->statusController = $statusController;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayment()
    {
        return $this->container->get($this->paymentServiceId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->container->get($this->storageServiceId);
    }

    /**
     * {@inheritdoc}
     */
    public function createStatusRequest(ModelInterface $model)
    {
        return new $this->statusRequestClass($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getInteractiveController()
    {
        return $this->interactiveController;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusController()
    {
        return $this->statusController;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->contextName;
    }
}