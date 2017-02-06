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
        if (empty($this->apiClass)) {
            throw new LogicException(sprintf('You must configure apiClass in __constructor method of the class the trait is applied to.'));
        }
        if (false == (class_exists($this->apiClass) || interface_exists($this->apiClass))) {
            throw new LogicException(sprintf('Api class not found or invalid class. "%s", $this->apiClass', $this->apiClass));
        }
        
        if (false == $api instanceof $this->apiClass) {
            throw new UnsupportedApiException(sprintf('Not supported api given. It must be an instance of %s', $this->apiClass));
        }

        $this->api = $api;
    }
}
