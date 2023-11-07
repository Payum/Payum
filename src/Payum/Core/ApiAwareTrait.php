<?php

namespace Payum\Core;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;
use function is_object;

trait ApiAwareTrait
{
    /**
     * @var mixed
     */
    protected $api;

    protected string|object|null $apiClass;

    public function setApi($api): void
    {
        if (empty($this->apiClass)) {
            throw new LogicException('You must configure apiClass in __constructor method of the class the trait is applied to.');
        }

        if (is_string($this->apiClass) && ! (class_exists($this->apiClass) || interface_exists($this->apiClass))) {
            throw new LogicException(sprintf('Api class not found or invalid class. "%s", $this->apiClass', $this->apiClass));
        }

        if (! $api instanceof $this->apiClass) {
            throw new UnsupportedApiException(sprintf('Not supported api given. It must be an instance of %s', is_object($this->apiClass) ? $this->apiClass::class : $this->apiClass));
        }

        $this->api = $api;
    }
}
