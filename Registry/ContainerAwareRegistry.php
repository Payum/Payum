<?php
namespace Payum\Bundle\PayumBundle\Registry;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Payum\Registry\AbstractRegistry;
use Payum\Exception\InvalidArgumentException;
use Payum\Storage\StorageInterface;

class ContainerAwareRegistry extends AbstractRegistry implements ContainerAwareInterface 
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * @param string $paymentName
     * 
     * @throws \Payum\Exception\InvalidArgumentException 
     * 
     * @return StorageInterface[]
     */
    public function getStorages($paymentName)
    {
        if (!isset($this->storages[$paymentName])) {
            throw new InvalidArgumentException(sprintf('Storages for payment %s not found.', $paymentName));
        }
        
        $storages = array();
        foreach ($this->storages[$paymentName] as $modelClass => $storageId) {
            $storages[$modelClass] = $this->getService($storageId);
        }
        
        return $storages;
    }
}