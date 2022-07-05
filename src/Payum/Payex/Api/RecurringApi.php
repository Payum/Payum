<?php

namespace Payum\Payex\Api;

class RecurringApi extends BaseApi
{
    public const PERIODTYPE_HOURS = 1;

    public const PERIODTYPE_DAILY = 2;

    public const PERIODTYPE_WEEKLY = 3;

    public const PERIODTYPE_MONTHLY = 4;

    public const PERIODTYPE_QUARTERLY = 5;

    public const PERIODTYPE_YEARLY = 6;

    public const RECURRINGSTATUS_RECURRING = 1;

    public const RECURRINGSTATUS_STOPPEDBYMERCHANT = 2;

    public const RECURRINGSTATUS_STOPPEDBYADMIN = 3;

    public const RECURRINGSTATUS_STOPPEDBYCLIENT = 4;

    public const RECURRINGSTATUS_STOPPEDBYSYSTEM = 5;

    public const RECURRINGSTATUS_FAILED = 6;

    /**
     * @link http://www.payexpim.com/technical-reference/pxrecurring/pxrecurring-start/
     *
     * @return array
     */
    public function start(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['account_number'];

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
            'notifyUrl',
        ));

        return $this->call('Start', $parameters, $this->getPxRecurringWsdl());
    }

    /**
     * @link http://www.payexpim.com/technical-reference/pxrecurring/pxrecurring-stop/
     *
     * @return array
     */
    public function stop(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['account_number'];

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'agreementRef',
        ));

        return $this->call('Stop', $parameters, $this->getPxRecurringWsdl());
    }

    /**
     * @link http://www.payexpim.com/technical-reference/pxrecurring/pxrecurring-stop/
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

        return $this->call('Check', $parameters, $this->getPxRecurringWsdl());
    }

    protected function getPxRecurringWsdl()
    {
        return $this->options['sandbox'] ?
            'https://test-external.payex.com/pxagreement/pxrecurring.asmx?wsdl' :
            'https://external.payex.com/pxagreement/pxrecurring.asmx?wsdl'
        ;
    }
}
