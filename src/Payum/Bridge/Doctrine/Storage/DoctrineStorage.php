<?php
namespace Payum\Bridge\Doctrine\Storage;

use Doctrine\Common\Persistence\ObjectManager;

use Payum\Storage\Identificator;
use Payum\Storage\StorageInterface;
use Payum\Exception\InvalidArgumentException;

class DoctrineStorage implements StorageInterface
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
    public function supportModel($model)
    {
        return 
            $model instanceof $this->modelClass ||
            (is_string($model) && $model === $this->modelClass)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function updateModel($model)
    {
        if (false == $this->supportModel($model)) {
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
    public function deleteModel($model)
    {
        if (false == $this->supportModel($model)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s',
                $this->modelClass
            ));
        }

        $this->objectManager->remove($model);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findModelById($id)
    {
        return $this->objectManager->find($this->modelClass, $id);
    }

    /**
     * {@inheritdoc}
     */
    function getIdentificator($model)
    {
        if (false == $this->supportModel($model)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s',
                $this->modelClass
            ));
        }
        
        $modelMetadata = $this->objectManager->getClassMetadata(get_class($model));
        $id = $modelMetadata->getIdentifierValues($model);
        if (count($id) > 1) {
            throw new \LogicException('Storage not support composite primary ids');
        }

        return new Identificator(array_shift($id), $model);
    }
}
