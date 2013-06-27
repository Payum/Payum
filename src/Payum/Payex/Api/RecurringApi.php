<?php
namespace Payum\Payex\Api;

class RecurringApi extends BaseApi
{
    const PERIODTYPE_HOURS = 1;

    const PERIODTYPE_DAILY = 2;

    const PERIODTYPE_WEEKLY = 3;

    const PERIODTYPE_MONTHLY = 4;

    const PERIODTYPE_QUARTERLY = 5;

    const PERIODTYPE_YEARLY = 6;
    
    /**
     * @link http://www.payexpim.com/technical-reference/pxrecurring/pxrecurring-start/
     * 
     * @param array $parameters
     * 
     * @return array
     */
    public function start(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['accountNumber'];

        if (isset($parameters['orderId'])) {
            $parameters['orderID'] = $parameters['orderId'];
            unset($parameters['orderId']);
        }

        //Deprecated, set to blank.
        $parameters['notifyUrl'] = '';

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'agreementRef',
            'startDate',
            'periodType',
            'period',
            'alertPeriod',
            'price',
            'productNumber',
            'orderID',
            'description',
            'notifyUrl'
        ));

        return $this->call('Start', $parameters, $this->getPxRecurringWsdl());
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getPxRecurringWsdl()
    {
        return $this->options['sandbox'] ?
            'https://test-external.payex.com/pxagreement/pxrecurring.asmx?wsdl' :
            'https://external.payex.com/pxagreement/pxrecurring.asmx?wsdl'
        ;
    }
}