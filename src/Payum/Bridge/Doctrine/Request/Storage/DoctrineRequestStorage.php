<?php
namespace Payum\Bridge\Doctrine\Request\Storage;

use Doctrine\Common\Persistence\ObjectManager;

use Payum\Request\Storage\RequestStorageInterface;
use Payum\Exception\InvalidArgumentException;

class DoctrineRequestStorage implements RequestStorageInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $requestClass;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $requestClass
     */
    public function __construct(ObjectManager $objectManager, $requestClass)
    {
        $this->objectManager = $objectManager;
        $this->requestClass = $requestClass;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function createRequest()
    {
        return new $this->requestClass;
    }

    /**
     * {@inheritdoc}
     */
    public function updateRequest($request)
    {
        if (false == $request instanceof $this->requestClass) {
            throw new InvalidArgumentException(sprintf(
                'Invalid request given. Should be instance of %s',
                $this->requestClass
            ));
        }
        
        $this->objectManager->persist($request);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findRequestById($id)
    {
        return $this->objectManager->find($this->requestClass, $id);
    }
}
