<?php
namespace Payum\Core;

use Payum\Core\Exception\UnsupportedApiException;

interface ApiAwareInterface
{
    /**
     * @param mixed $api
     *
     * @throws UnsupportedApiException if the given Api is not supported.
     */
    public function setApi($api);
}
