<?php
namespace Payum\Payex\Api;

class AgreementApi extends BaseApi
{
    const AGREEMENTSTATUS_NOTVERIFIED = 0;

    const AGREEMENTSTATUS_VERIFIED = 1;

    const AGREEMENTSTATUS_DELETED = 2;

    /**
     * @param array $parameters
     *
     * @return array
     */
    public function create(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['account_number'];

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
            'stopDate',
        ));

        return $this->call('CreateAgreement3', $parameters, $this->getPxAgreementWsdl());
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    public function check(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['account_number'];

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'agreementRef',
        ));

        return $this->call('Check', $parameters, $this->getPxAgreementWsdl());
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    public function delete(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['account_number'];

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'agreementRef',
        ));

        return $this->call('DeleteAgreement', $parameters, $this->getPxAgreementWsdl());
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    public function autoPay(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['account_number'];

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'agreementRef',
            'price',
            'productNumber',
            'description',
            'orderId',
            'purchaseOperation',
            'currency',
        ));

        return $this->call('AutoPay3', $parameters, $this->getPxAgreementWsdl());
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
