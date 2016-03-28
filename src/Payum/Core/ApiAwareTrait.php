<?php
namespace Payum\Core;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;

trait ApiAwareTrait
{
    /**
     * @var mixed
     */
    protected $api;

    /**
     * @var string
     */
    protected $apiClass; 

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == class_exists($this->apiClass)) {
            throw new LogicException(sprintf('Invalid api class given: "%s"', $this->apiClass));
        }
        
        if (false == $api instanceof $this->apiClass) {
            throw new UnsupportedApiException(sprintf('Not supported api given. It must be istance of %s', $this->apiClass));
        }

        $this->api = $api;
    }
}