<?php
namespace Payum\Payex\Api;

class AgreementApi extends BaseApi
{
    /**
     * @param array $parameters
     * 
     * @return array
     */
    public function create(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['accountNumber'];

        //Deprecated, set to blank.
        $parameters['notifyUrl'] = '';

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'merchantRef',
            'description',
            'purchaseOperation',
            'maxAmount',
            'notifyUrl',
            'startDate',
            'stopDate'
        ));

        return $this->call('CreateAgreement3', $parameters, $this->getPxAgreementWsdl());
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getPxAgreementWsdl()
    {
        return $this->options['sandbox'] ? 
            'https://test-external.payex.com/pxagreement/pxagreement.asmx?wsdl' :
            'https://external.payex.com/pxagreement/pxagreement.asmx?wsdl'
        ;
    }
}