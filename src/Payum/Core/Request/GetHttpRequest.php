<?php

namespace Payum\Core\Request;

use AllowDynamicProperties;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A handler reads the inbound request as PSR-7 from
 *             Payum\Core\Handler\Context::httpRequest() instead.
 */
#[AllowDynamicProperties]
class GetHttpRequest
{
    /**
     * @var array
     */
    public $query = [];

    /**
     * @var array
     */
    public $request = [];

    /**
     * @var string
     */
    public $method = '';

    /**
     * @var string
     */
    public $uri = '';

    /**
     * @var string
     */
    public $clientIp = '';

    /**
     * @var string
     */
    public $userAgent = '';

    /**
     * @var string
     */
    public $content = '';

    /**
     * @var array
     */
    public $headers = [];

    public function __construct()
    {
    }
}
