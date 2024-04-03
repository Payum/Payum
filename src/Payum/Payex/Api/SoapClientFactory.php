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

    /**
     * @param string $wsdl
     *
     * @return SoapClient
     */
    public function createWsdlClient($wsdl)
    {
        return new $this->soapClientClass($wsdl, $this->soapClientOptions);
    }
}
