<?php

namespace Payum\Core;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;
use function is_object;
use function trigger_error;

/**
 * @deprecated since 2.0. Add the API class to the action constructor instead
 */
trait ApiAwareTrait
{
    /**
     * @var mixed
     * @deprecated since 2.0. BC will be removed in 3.x. Use dependency-injection to inject the api instead.
     */
    protected $api;

    /**
     * @deprecated since 2.0. BC will be removed in 3.x. Use dependency-injection to inject the api instead.
     */
    protected string|object|null $apiClass;

    /**
     * @deprecated since 2.0. BC will be removed in 3.x. Use dependency-injection to inject the api instead.
     */
    public function setApi($api): void
    {
        @trigger_error(sprintf('The method %s is deprecated since 2.0. Use dependency-injection to inject the api instead.', __METHOD__), E_USER_DEPRECATED);

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
