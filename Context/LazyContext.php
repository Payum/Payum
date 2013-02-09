<?php
namespace Payum\Bundle\PayumBundle\Context;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

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
    protected $captureInteractiveController;

    /**
     * @var string
     */
    protected $captureFinishedController;
    
    /**
     * @param string $contextName
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(
        $contextName, 
        $paymentServiceId, 
        $storageServiceId, 
        $statusRequestClass,
        $captureInteractiveController,
        $captureFinishedController
    ) {
        $this->contextName = $contextName;
        $this->paymentServiceId = $paymentServiceId;
        $this->storageServiceId = $storageServiceId;
        $this->statusRequestClass = $statusRequestClass;
        $this->captureInteractiveController = $captureInteractiveController;
        $this->captureFinishedController = $captureFinishedController;
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
    public function createStatusRequest($model)
    {
        return new $this->statusRequestClass($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptureInteractiveController()
    {
        return $this->captureInteractiveController;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptureFinishedController()
    {
        return $this->captureFinishedController;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->contextName;
    }
}