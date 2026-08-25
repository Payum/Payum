<?php

namespace Payum\Core;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;
use function is_object;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated and will be removed in 3.0. Take the api as a constructor argument instead, and let the container inject it.', ApiAwareTrait::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Take the api as a constructor argument instead, and
 *             let the container inject it.
 */
trait ApiAwareTrait
{
    /**
     * @var mixed
     * @deprecated since 2.0.0, will be removed in 3.0. Take the api as a constructor argument instead.
     */
    protected $api;

    /**
     * @deprecated since 2.0.0, will be removed in 3.0. Take the api as a constructor argument instead.
     */
    protected string|object|null $apiClass;

    /**
     * @deprecated since 2.0.0, will be removed in 3.0. Take the api as a constructor argument instead.
     */
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
