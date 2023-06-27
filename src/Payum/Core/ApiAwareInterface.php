<?php

namespace Payum\Core;

use Payum\Core\Exception\UnsupportedApiException;

/**
 * @template T
 */
interface ApiAwareInterface
{
    /**
     * @param T $api
     *
     * @throws UnsupportedApiException if the given Api is not supported.
     */
    public function setApi(object $api): void;
}
