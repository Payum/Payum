<?php
namespace Payum\Bridge\Doctrine\Storage;

use Doctrine\Common\Persistence\ObjectManager;

use Payum\Domain\Storage\ModelStorageInterface;
use Payum\Domain\ModelInterface;
use Payum\Exception\InvalidArgumentException;

class DoctrineModelStorage implements ModelStorageInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $modelClass
     */
    public function __construct(ObjectManager $objectManager, $modelClass)
    {
        $this->objectManager = $objectManager;
        $this->modelClass = $modelClass;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function createModel()
    {
        return new $this->modelClass;
    }

    /**
     * {@inheritdoc}
     */
    public function updateModel(ModelInterface $model)
    {
        if (false == $model instanceof $this->modelClass) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s',
                $this->modelClass
            ));
        }
        
        $this->objectManager->persist($model);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findModelById($id)
    {
        return $this->objectManager->find($this->modelClass, $id);
    }
}
