<?php
namespace Payum\Request\Storage;

use Payum\Exception\InvalidArgumentException;

class FilesystemRequestStorage implements RequestStorageInterface
{
    protected $storageDir;

    protected $requestClass;

    protected $idProperty;

    public function __construct($storageDir, $requestClass, $idProperty)
    {
        $this->storageDir = $storageDir;
        $this->requestClass = $requestClass;
        $this->idProperty = $idProperty;
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

        $rp = new \ReflectionProperty($request, $this->idProperty);
        
        $rp->setAccessible(true);
        $id = $rp->getValue($request);
        $rp->setAccessible(false);
        if (false == $id) {
            $id = uniqid();

            $rp->setAccessible(true);
            $rp->setValue($request, $id);
            $rp->setAccessible(false);
        }

        file_put_contents($this->storageDir.'/request-'.$id, serialize($request));
    }

    /**
     * {@inheritdoc}
     */
    public function findRequestById($id)
    {
        if (file_exists($this->storageDir.'/request-'.$id)) {
            return unserialize(file_get_contents($this->storageDir.'/request-'.$id));
        }
    }
}