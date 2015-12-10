<?php
namespace Payum\Payex\Api;

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
     * @param array  $soapClientOptions
     * @param string $soapClientClass
     */
    public function __construct(array $soapClientOptions = array(), $soapClientClass = null)
    {
        $soapClientOptions = array_replace(
            array(
                'trace' => true,
                'exceptions' => true,
            ),
            $soapClientOptions
        );

        $this->soapClientClass = $soapClientClass ?: 'SoapClient';
        $this->soapClientOptions = $soapClientOptions;
    }

    /**
     * @param string $wsdl
     *
     * @return \SoapClient
     */
    public function createWsdlClient($wsdl)
    {
        return new $this->soapClientClass($wsdl, $this->soapClientOptions);
    }
}
