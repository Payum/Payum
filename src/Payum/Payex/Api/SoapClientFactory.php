<?php

namespace Payum\Payex\Api;

use SoapClient;

class SoapClientFactory
{
    /**
     * @var string
     */
    protected $soapClientClass;

    /**
     * @var array
     */
    protected $soapClientOptions;

    /**
     * @param string $soapClientClass
     * @param mixed[] $soapClientOptions
     */
    public function __construct(array $soapClientOptions = [], $soapClientClass = null)
    {
        $soapClientOptions = array_replace(
            [
                'trace' => true,
                'exceptions' => true,
            ],
            $soapClientOptions
        );

        $this->soapClientClass = $soapClientClass ?: SoapClient::class;
        $this->soapClientOptions = $soapClientOptions;
    }

    public function createWsdlClient(string $wsdl): object
    {
        return new $this->soapClientClass($wsdl, $this->soapClientOptions);
    }
}
